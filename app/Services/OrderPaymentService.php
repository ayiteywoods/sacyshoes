<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function __construct(
        protected OrderNotificationService $notifications,
        protected StockReservationService $reservations
    ) {}

    public function markAsPaid(Order $order, Payment $payment, array $data = []): void
    {
        $wasAlreadyPaid = false;

        DB::transaction(function () use ($order, $payment, $data, &$wasAlreadyPaid) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($order->status === OrderStatus::Cancelled) {
                return;
            }

            if ($order->payment_status === PaymentStatus::Paid) {
                $wasAlreadyPaid = true;

                return;
            }

            $paidAt = isset($data['paid_at'])
                ? Carbon::parse($data['paid_at'])
                : now();

            $transactionId = isset($data['id']) ? (string) $data['id'] : $payment->provider_transaction_id;
            $receiptNumber = filled($data['receipt_number'] ?? null)
                ? (string) $data['receipt_number']
                : $transactionId;
            $displayReference = $receiptNumber
                ? $order->order_number.'-'.$receiptNumber
                : null;

            $payment->update([
                'status' => PaymentStatus::Paid,
                'channel' => $data['channel'] ?? data_get($data, 'authorization.channel') ?? $payment->channel,
                'provider_transaction_id' => $transactionId ?: $payment->provider_transaction_id,
                'paid_at' => $paidAt,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'verification' => $data,
                    'display_reference' => $displayReference,
                ]),
            ]);

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Paid,
                'paid_at' => $paidAt,
            ]);

            $order->loadMissing('items');

            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::query()->find($item->product_variant_id);

                    if ($variant) {
                        $this->reservations->fulfill($variant, $item->quantity);
                    }

                    continue;
                }

                if ($item->product_id) {
                    Product::query()
                        ->whereKey($item->product_id)
                        ->where('quantity', '>=', $item->quantity)
                        ->decrement('quantity', $item->quantity);
                }
            }
        });

        if ($wasAlreadyPaid) {
            return;
        }

        $order->refresh();
        $payment->refresh();

        $this->notifications->paymentReceived($order);
        app(AdminNotificationService::class)->sync();
    }
}
