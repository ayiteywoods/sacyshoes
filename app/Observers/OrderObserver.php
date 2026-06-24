<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderNotificationService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $previousStatus = $order->getOriginal('status');

        if (! $previousStatus instanceof OrderStatus) {
            $previousStatus = OrderStatus::from($order->getRawOriginal('status'));
        }

        app(OrderNotificationService::class)->orderStatusChanged(
            $order,
            $previousStatus,
        );
    }
}
