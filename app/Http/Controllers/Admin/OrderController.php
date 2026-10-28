<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AdminNotificationService;
use App\Services\OrderPaymentService;
use App\Services\PaystackPaymentReconciliationService;
use App\Services\StockReservationService;
use App\Support\AdminTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $perPageOptions = [20, 50, 100];
        $perPage = $request->integer('per_page', 20);

        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 20;
        }

        $query = Order::query()->with('user');

        if ($request->filled('payment_status')) {
            $paymentStatus = $request->string('payment_status')->toString();

            if ($paymentStatus === OrderStatus::Cancelled->value) {
                $query->where('status', OrderStatus::Cancelled);
            } elseif (in_array($paymentStatus, ['paid', 'pending', 'failed', 'refunded'], true)) {
                $query->where('payment_status', $paymentStatus);
            }
        }

        $orders = AdminTable::paginate(
            $query,
            $request,
            [
                'order_number' => 'order_number',
                'customer' => 'billing_full_name',
                'total' => 'total',
                'payment_status' => 'payment_status',
                'status' => 'status',
                'created_at' => 'created_at',
            ],
            'created_at',
            'desc',
            $perPage,
        );

        $paymentFilter = $request->string('payment_status')->toString();

        return view('admin.orders.index', compact('orders', 'paymentFilter', 'perPage', 'perPageOptions'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.product', 'payment']);

        return view('admin.orders.show', [
            'order' => $order,
            'trackingSteps' => $order->trackingSteps(),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $allowedStatuses = collect($order->adminStatusOptions())
            ->map(fn (OrderStatus $status) => $status->value)
            ->all();

        $validated = $request->validate([
            'status' => ['required', Rule::in($allowedStatuses)],
        ]);

        $previousStatus = $order->status;
        $newStatus = OrderStatus::from($validated['status']);

        if ($newStatus === $previousStatus && ! ($newStatus === OrderStatus::Refunded && $order->payment_status !== PaymentStatus::Refunded)) {
            return back()->with('success', 'No changes were made.');
        }

        if ($newStatus === OrderStatus::Paid && $order->payment_status !== PaymentStatus::Paid) {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->latest('id')
                ->first();

            if (! $payment) {
                $payment = Payment::query()->create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'reference' => $order->order_number.'_admin_'.time(),
                    'provider' => 'manual',
                    'amount' => $order->total,
                    'currency' => config('shop.currency'),
                    'status' => PaymentStatus::Pending,
                    'metadata' => [
                        'marked_paid_by' => auth()->id(),
                        'source' => 'admin_status_update',
                    ],
                ]);
            }

            app(OrderPaymentService::class)->markAsPaid($order, $payment, [
                'id' => $payment->provider_transaction_id ?: 'admin-'.$order->id.'-'.time(),
                'channel' => $payment->channel ?: 'admin',
                'paid_at' => now()->toIso8601String(),
                'receipt_number' => 'ADMIN-'.$order->order_number,
                'metadata' => [
                    'marked_paid_by' => auth()->id(),
                    'source' => 'admin_status_update',
                ],
            ]);

            $order->refresh();

            if ($order->payment_status !== PaymentStatus::Paid) {
                return back()->with('error', 'This item has already been sold. Payment was not recorded.');
            }

            return back()->with('success', 'Order marked as paid and payment status updated.');
        }

        if ($newStatus === OrderStatus::Cancelled) {
            app(StockReservationService::class)->releaseOrder($order);
        }

        if ($newStatus === OrderStatus::Refunded) {
            DB::transaction(function () use ($order) {
                $payment = Payment::query()
                    ->where('order_id', $order->id)
                    ->latest('id')
                    ->first();

                if (! $payment) {
                    $payment = Payment::query()->create([
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'reference' => $order->order_number.'_refund_'.time(),
                        'provider' => 'manual',
                        'amount' => $order->total,
                        'currency' => config('shop.currency'),
                        'status' => PaymentStatus::Refunded,
                        'metadata' => [
                            'marked_refunded_by' => auth()->id(),
                            'source' => 'admin_status_update',
                        ],
                    ]);
                } else {
                    $payment->update([
                        'status' => PaymentStatus::Refunded,
                        'metadata' => array_merge($payment->metadata ?? [], [
                            'marked_refunded_by' => auth()->id(),
                            'refunded_at' => now()->toIso8601String(),
                            'source' => 'admin_status_update',
                        ]),
                    ]);
                }

                $order->update([
                    'status' => OrderStatus::Refunded,
                    'payment_status' => PaymentStatus::Refunded,
                ]);
            });

            app(AdminNotificationService::class)->sync();

            return back()->with('success', 'Order marked as refunded and payment status updated.');
        }

        $order->update(['status' => $newStatus]);

        app(AdminNotificationService::class)->sync();

        return back()->with('success', $order->isTrackable()
            ? 'Delivery tracking updated. The customer has been emailed about this stage.'
            : 'Order status updated.');
    }

    public function syncPaystack(
        Order $order,
        PaystackPaymentReconciliationService $reconciliation,
    ): RedirectResponse {
        if ($order->payment_status === PaymentStatus::Paid) {
            return back()->with('success', 'This order is already marked as paid.');
        }

        if ($reconciliation->reconcileOrder($order)) {
            return back()->with('success', 'Payment verified with Paystack and order marked as paid.');
        }

        return back()->with('error', 'No successful Paystack payment was found for this order.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        if ($order->payment_status !== PaymentStatus::Paid) {
            app(StockReservationService::class)->releaseOrder($order);
        }

        $order->delete();

        app(AdminNotificationService::class)->sync();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
