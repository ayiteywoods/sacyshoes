<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\URL;

class GuestOrderAccess
{
    public static function canAccess(Order $order): bool
    {
        if (request()->hasValidSignature()) {
            return true;
        }

        $user = auth()->user();

        if ($user && (int) $order->user_id === (int) $user->id) {
            return true;
        }

        if ($order->user_id !== null) {
            return false;
        }

        $guestOrderIds = array_map(
            static fn ($id) => (int) $id,
            session('guest_order_ids', [])
        );

        return in_array((int) $order->id, $guestOrderIds, true);
    }

    public static function remember(Order $order): void
    {
        if (auth()->check() || $order->user_id !== null) {
            return;
        }

        $orderIds = array_map(
            static fn ($id) => (int) $id,
            session('guest_order_ids', [])
        );
        $orderIds[] = (int) $order->id;

        session(['guest_order_ids' => array_values(array_unique($orderIds))]);
    }

    public static function assertCanAccess(Order $order): void
    {
        abort_unless(self::canAccess($order), 403);
    }

    public static function paystackInitializeUrl(Order $order): string
    {
        $expiresAt = $order->payment_due_at ?? now()->addDay();

        if ($expiresAt->isPast()) {
            $expiresAt = now()->addHours(2);
        }

        return URL::temporarySignedRoute(
            'paystack.initialize',
            $expiresAt,
            ['order' => $order->id],
        );
    }
}
