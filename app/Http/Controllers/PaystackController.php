<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderPaymentService;
use App\Services\PaystackPaymentReconciliationService;
use App\Services\PaystackService;
use App\Support\GuestOrderAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaystackController extends Controller
{
    public function __construct(
        protected PaystackService $paystack,
        protected OrderPaymentService $payments,
        protected PaystackPaymentReconciliationService $reconciliation,
    ) {}

    public function initialize(Order $order): RedirectResponse
    {
        GuestOrderAccess::assertCanAccess($order);

        if ($order->payment_status === PaymentStatus::Paid) {
            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'This order has already been paid.');
        }

        if ($order->status === OrderStatus::Cancelled) {
            if ($this->reconciliation->reconcileOrder($order)) {
                return redirect()
                    ->route('checkout.success', $order)
                    ->with('success', 'Payment confirmed. Thank you!');
            }

            $order->update([
                'status' => OrderStatus::PendingPayment,
                'payment_status' => PaymentStatus::Pending,
                'payment_due_at' => now()->addHours((int) config('shop.order_payment_timeout_hours', 24)),
            ]);
        }

        $reference = $this->resolvePaymentReference($order);
        $callbackUrl = $this->paystack->callbackUrl();

        $data = $this->paystack->initialize([
            'email' => $order->customerEmail() ?? $order->billing_email,
            'amount' => (int) round(((float) $order->total) * 100),
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'currency' => config('shop.currency'),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'custom_fields' => [
                    [
                        'display_name' => 'Order Number',
                        'variable_name' => 'order_number',
                        'value' => $order->order_number,
                    ],
                    [
                        'display_name' => 'Order ID',
                        'variable_name' => 'order_id',
                        'value' => (string) $order->id,
                    ],
                ],
            ],
        ]);

        Payment::query()->updateOrCreate(
            ['reference' => $data['reference']],
            [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => 'paystack',
                'amount' => $order->total,
                'currency' => config('shop.currency'),
                'status' => PaymentStatus::Pending,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'access_code' => $data['access_code'],
                    'initialization_reference' => $reference,
                ],
            ]
        );

        return redirect()->away($data['authorization_url']);
    }

    public function callback(Request $request): RedirectResponse
    {
        $reference = (string) $request->query('reference', '');

        if ($reference === '') {
            throw ValidationException::withMessages([
                'paystack' => 'Missing payment reference.',
            ]);
        }

        try {
            $data = $this->paystack->verify($reference);
        } catch (Throwable $exception) {
            Log::error('Paystack callback verify failed.', [
                'reference' => $reference,
                'paystack_mode' => $this->paystack->mode(),
                'error' => $exception->getMessage(),
            ]);

            $payment = $this->reconciliation->findPaymentByReference($reference);

            if ($payment?->order) {
                GuestOrderAccess::remember($payment->order);
                $this->reconciliation->reconcileOrder($payment->order);

                return redirect()
                    ->route('checkout.success', $payment->order)
                    ->with('error', 'We are confirming your payment. Please check back in a moment.');
            }

            throw ValidationException::withMessages([
                'paystack' => 'Unable to verify Paystack transaction.',
            ]);
        }

        $payment = $this->reconciliation->resolvePayment($reference, $data);

        if (! $payment) {
            Log::error('Paystack callback could not match payment to order.', [
                'reference' => $reference,
                'order_id' => $this->reconciliation->orderIdFromPaystackData($data),
                'order_number' => $this->reconciliation->orderNumberFromPaystackData($data),
            ]);

            throw ValidationException::withMessages([
                'paystack' => 'Payment could not be matched to an order.',
            ]);
        }

        $order = $payment->order()->firstOrFail();

        GuestOrderAccess::remember($order);

        if (($data['status'] ?? null) === 'success') {
            $result = $this->reconciliation->applySuccessfulPaystackPayment($order, $payment, $data);
            $order->refresh();

            if (! $result['reconciled']) {
                Log::error('Paystack callback payment verified but order not marked paid.', [
                    'order_id' => $order->id,
                    'reference' => $reference,
                    'reason' => $result['reason'],
                ]);
            }

            return redirect()
                ->route('checkout.success', $order)
                ->with(
                    $order->payment_status === PaymentStatus::Paid ? 'success' : 'error',
                    $order->payment_status === PaymentStatus::Paid
                        ? 'Payment confirmed. Thank you!'
                        : 'Payment received. We are finalizing your order — please refresh shortly.'
                );
        }

        $payment->update([
            'status' => PaymentStatus::Failed,
            'channel' => $data['channel'] ?? null,
            'metadata' => array_merge($payment->metadata ?? [], [
                'verification' => $data,
            ]),
        ]);

        return redirect()
            ->route('checkout.success', $order)
            ->with('error', 'Payment was not successful. Please try again.');
    }

    protected function resolvePaymentReference(Order $order): string
    {
        // Always use a fresh reference. Reusing abandoned/failed Paystack
        // references can leave successful retries unmatched on the dashboard.
        return $order->order_number.'_'.time().'_'.bin2hex(random_bytes(2));
    }
}
