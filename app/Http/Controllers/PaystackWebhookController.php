<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Payment;
use App\Services\OrderPaymentService;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request, OrderPaymentService $payments)
    {
        $signature = (string) $request->header('x-paystack-signature', '');
        $payload = (string) $request->getContent();

        if ($signature === '' || $payload === '') {
            return response()->noContent();
        }

        $expected = app(PaystackService::class)->computeWebhookSignature($payload);

        if (! hash_equals($expected, $signature)) {
            Log::warning('Paystack webhook signature mismatch');

            return response()->noContent();
        }

        $event = $request->json()->all();
        $eventType = $event['event'] ?? null;
        $data = $event['data'] ?? [];

        if ($eventType !== 'charge.success') {
            return response()->noContent();
        }

        $reference = $data['reference'] ?? null;

        if (! $reference) {
            return response()->noContent();
        }

        $payment = Payment::query()->where('reference', $reference)->first();

        if (! $payment) {
            return response()->noContent();
        }

        if ($payment->status->value === 'paid') {
            return response()->noContent();
        }

        $order = $payment->order;

        if (! $order || $order->status === OrderStatus::Cancelled) {
            return response()->noContent();
        }

        $payment->update([
            'metadata' => array_merge($payment->metadata ?? [], [
                'webhook' => $event,
            ]),
        ]);

        $payments->markAsPaid($order, $payment, $data);

        return response()->noContent();
    }
}
