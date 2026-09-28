<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Services\PaystackPaymentReconciliationService;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaystackWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PaystackPaymentReconciliationService $reconciliation,
    ) {
        $signature = (string) $request->header('x-paystack-signature', '');
        $payload = (string) $request->getContent();
        $paystack = app(PaystackService::class);

        if ($signature === '' || $payload === '') {
            Log::warning('Paystack webhook missing signature or payload.', [
                'has_signature' => $signature !== '',
                'payload_length' => strlen($payload),
                'paystack_mode' => $paystack->mode(),
            ]);

            return response()->noContent();
        }

        $expected = $paystack->computeWebhookSignature($payload);

        if (! hash_equals($expected, $signature)) {
            Log::warning('Paystack webhook signature mismatch.', [
                'paystack_mode' => $paystack->mode(),
                'payload_length' => strlen($payload),
            ]);

            return response()->noContent();
        }

        $event = $request->json()->all();
        $eventType = $event['event'] ?? null;
        $data = $event['data'] ?? [];

        Log::info('Paystack webhook received.', [
            'event' => $eventType,
            'reference' => $data['reference'] ?? null,
            'paystack_mode' => $paystack->mode(),
        ]);

        if ($eventType !== 'charge.success') {
            return response()->noContent();
        }

        $reference = $data['reference'] ?? null;

        if (! $reference) {
            Log::warning('Paystack webhook charge.success missing reference.');

            return response()->noContent();
        }

        try {
            $payment = $reconciliation->resolvePayment($reference, $data);
        } catch (Throwable $exception) {
            Log::error('Paystack webhook failed resolving payment.', [
                'reference' => $reference,
                'error' => $exception->getMessage(),
            ]);

            return response()->noContent();
        }

        if (! $payment) {
            Log::error('Paystack webhook could not match payment to an order.', [
                'reference' => $reference,
                'order_id' => $reconciliation->orderIdFromPaystackData($data),
                'order_number' => $reconciliation->orderNumberFromPaystackData($data),
                'customer_email' => data_get($data, 'customer.email'),
            ]);

            return response()->noContent();
        }

        if ($payment->status === PaymentStatus::Paid) {
            Log::info('Paystack webhook ignored already-paid payment.', [
                'reference' => $reference,
                'order_id' => $payment->order_id,
            ]);

            return response()->noContent();
        }

        $order = $payment->order;

        if (! $order) {
            Log::error('Paystack webhook payment has no order.', [
                'reference' => $reference,
                'payment_id' => $payment->id,
            ]);

            return response()->noContent();
        }

        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'webhook' => $event,
            ]),
        ]);

        try {
            $result = $reconciliation->applySuccessfulPaystackPayment($order, $payment, $data);
        } catch (Throwable $exception) {
            Log::error('Paystack webhook mark-as-paid threw exception.', [
                'reference' => $reference,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $exception->getMessage(),
            ]);

            return response()->noContent();
        }

        if (! $result['reconciled']) {
            Log::error('Paystack webhook payment verified but order not marked paid.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reference' => $reference,
                'reason' => $result['reason'],
            ]);
        } else {
            Log::info('Paystack webhook marked order as paid.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reference' => $reference,
            ]);
        }

        return response()->noContent();
    }
}
