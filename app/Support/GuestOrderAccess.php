<?php

namespace App\Support;

use App\Models\Order;

class GuestOrderAccess
{
    public static function canAccess(Order $order): bool
    {
        if (request()->hasValidSignature()) {
            return true;
        }

        $user = auth()->user();

        if ($user && $order->user_id === $user->id) {
            return true;
        }

        if ($order->user_id !== null) {
            return false;
        }

        return in_array($order->id, session('guest_order_ids', []), true);
    }

    public static function remember(Order $order): void
    {
        if (auth()->check() || $order->user_id !== null) {
            return;
        }

        $orderIds = session('guest_order_ids', []);
        $orderIds[] = $order->id;

        session(['guest_order_ids' => array_values(array_unique($orderIds))]);
    }

    public static function assertCanAccess(Order $order): void
    {
        abort_unless(self::canAccess($order), 403);
    }
}
