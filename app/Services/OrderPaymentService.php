<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function __construct(
        protected OrderNotificationService $notifications
    ) {}

    public function markAsPaid(Order $order, Payment $payment, array $data = []): void
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            return;
        }

        DB::transaction(function () use ($order, $payment, $data) {
            $paidAt = now();

            $payment->update([
                'status' => PaymentStatus::Paid,
                'channel' => $data['channel'] ?? $payment->channel,
                'paid_at' => $paidAt,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'verification' => $data,
                ]),
            ]);

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Paid,
                'paid_at' => $paidAt,
            ]);

            $order->loadMissing('items');

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::query()
                        ->whereKey($item->product_id)
                        ->where('quantity', '>=', $item->quantity)
                        ->decrement('quantity', $item->quantity);
                }
            }
        });

        $order->refresh();
        $this->notifications->paymentReceived($order);
        app(AdminNotificationService::class)->sync();
    }
}
