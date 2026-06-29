<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderCancellationService
{
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
