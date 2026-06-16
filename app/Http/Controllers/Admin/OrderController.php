<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\AdminTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = AdminTable::paginate(
            Order::query()->with('user'),
            $request,
            [
                'order_number' => 'order_number',
                'customer' => 'billing_full_name',
                'total' => 'total',
                'payment_status' => 'payment_status',
                'status' => 'status',
                'created_at' => 'created_at',
            ],
        );

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.product', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $previousStatus = $order->status;

        $order->update(['status' => $validated['status']]);

        app(\App\Services\OrderNotificationService::class)->orderStatusUpdated($order->fresh(), $previousStatus);
        app(\App\Services\AdminNotificationService::class)->sync();

        return back()->with('success', 'Order status updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        app(\App\Services\AdminNotificationService::class)->sync();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order deleted successfully.');
    }
}
