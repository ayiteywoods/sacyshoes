<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderCancellationService
{
    public function __construct(
        protected StockReservationService $reservations,
    ) {}

    public function cancelUnpaid(Order $order): bool
    {
        if ($order->status !== OrderStatus::PendingPayment) {
            return false;
        }

        if ($order->payment_status !== PaymentStatus::Pending) {
            return false;
        }

        if ($order->payment_due_at && $order->payment_due_at->isFuture()) {
            return false;
        }

        DB::transaction(function () use ($order) {
            $order->loadMissing('items');

            foreach ($order->items as $item) {
                if (! $item->product_variant_id) {
                    continue;
                }

                $variant = ProductVariant::query()->find($item->product_variant_id);

                if ($variant) {
                    $this->reservations->release($variant, $item->quantity);
                }
            }

            $order->update([
                'status' => OrderStatus::Cancelled,
                'payment_status' => PaymentStatus::Failed,
            ]);
        });

        $order->refresh();
        app(AdminNotificationService::class)->sync();

        return true;
    }
}
