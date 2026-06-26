<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderPaymentService;
use App\Services\PaystackService;
use App\Support\GuestOrderAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaystackController extends Controller
{
    public function __construct(
        protected PaystackService $paystack,
        protected OrderPaymentService $payments,
    ) {}

    public function initialize(Order $order): RedirectResponse
    {
        GuestOrderAccess::assertCanAccess($order);

        if ($order->status === OrderStatus::Cancelled) {
            return redirect()
                ->route('checkout.success', $order)
                ->with('error', 'This order was cancelled because payment was not received in time.');
        }

        if ($order->payment_status->value === 'paid') {
            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'This order has already been paid.');
        }

        $reference = $this->resolvePaymentReference($order);

        $payment = Payment::query()->updateOrCreate(
            ['reference' => $reference],
            [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => 'paystack',
                'amount' => $order->total,
                'currency' => config('shop.currency'),
                'status' => PaymentStatus::Pending,
                'metadata' => [
                    'order_number' => $order->order_number,
                ],
            ]
        );

        $callbackUrl = $this->paystack->callbackUrl();

        $data = $this->paystack->initialize([
            'email' => $order->customerEmail() ?? $order->billing_email,
            'amount' => (int) round(((float) $order->total) * 100),
            'reference' => $payment->reference,
            'callback_url' => $callbackUrl,
            'currency' => config('shop.currency'),
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'access_code' => $data['access_code'],
            ]),
        ]);

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

        $payment = Payment::query()->where('reference', $reference)->firstOrFail();
        $order = $payment->order()->firstOrFail();

        GuestOrderAccess::remember($order);

        $data = $this->paystack->verify($reference);

        if (($data['status'] ?? null) === 'success') {
            $this->payments->markAsPaid($order, $payment, $data);

            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'Payment confirmed. Thank you!');
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
        $latestPayment = Payment::query()
            ->where('order_id', $order->id)
            ->latest('id')
            ->first();

        if ($latestPayment?->status === PaymentStatus::Pending) {
            return $latestPayment->reference;
        }

        return $order->order_number.'_'.time();
    }
}
