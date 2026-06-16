<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderPaymentService;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaystackController extends Controller
{
    public function __construct(
        protected PaystackService $paystack,
        protected OrderPaymentService $payments,
    ) {}

    public function initialize(Order $order): RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status->value === 'paid') {
            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'This order has already been paid.');
        }

        $reference = $order->payment?->reference ?? $this->makeReference($order);

        $payment = Payment::query()->updateOrCreate(
            ['reference' => $reference],
            [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => 'paystack',
                'amount' => $order->total,
                'currency' => config('shop.currency'),
                'status' => \App\Enums\PaymentStatus::Pending,
                'metadata' => [
                    'order_number' => $order->order_number,
                ],
            ]
        );

        $callbackUrl = route('paystack.callback');

        $data = $this->paystack->initialize([
            'email' => $order->billing_email,
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

        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }

        $order = $payment->order()->firstOrFail();

        $data = $this->paystack->verify($reference);

        if (($data['status'] ?? null) === 'success') {
            $this->payments->markAsPaid($order, $payment, $data);

            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'Payment confirmed. Thank you!');
        }

        $payment->update([
            'status' => \App\Enums\PaymentStatus::Failed,
            'channel' => $data['channel'] ?? null,
            'metadata' => array_merge($payment->metadata ?? [], [
                'verification' => $data,
            ]),
        ]);

        return redirect()
            ->route('checkout.success', $order)
            ->with('error', 'Payment was not successful. Please try again.');
    }

    protected function makeReference(Order $order): string
    {
        return 'SACY-'.str_replace('-', '', (string) Str::uuid()).'-'.$order->id;
    }
}
