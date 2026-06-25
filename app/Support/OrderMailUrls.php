<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\URL;

class OrderMailUrls
{
    public static function viewOrder(Order $order): string
    {
        if ($order->user_id) {
            return route('account.orders.show', $order);
        }

        return URL::temporarySignedRoute(
            'checkout.success',
            now()->addDays(30),
            ['order' => $order->id]
        );
    }

    public static function payOrder(Order $order): string
    {
        return GuestOrderAccess::paystackInitializeUrl($order);
    }

    public static function invoice(Order $order): string
    {
        if ($order->user_id) {
            return route('orders.invoice', $order);
        }

        return URL::temporarySignedRoute(
            'orders.invoice',
            now()->addDays(30),
            ['order' => $order->id]
        );
    }

    public static function invoicePdf(Order $order): string
    {
        if ($order->user_id) {
            return route('orders.invoice.pdf', $order);
        }

        return URL::temporarySignedRoute(
            'orders.invoice.pdf',
            now()->addDays(30),
            ['order' => $order->id]
        );
    }
}
