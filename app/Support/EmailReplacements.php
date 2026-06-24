<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class EmailReplacements
{
    /**
     * @return array<string, string>
     */
    public static function forUser(User $user): array
    {
        return [
            'first_name' => (string) ($user->first_name ?? $user->name),
            'customer_name' => (string) $user->name,
            'store_name' => (string) config('shop.store_name', MailBranding::storeName()),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function forOrder(Order $order): array
    {
        return [
            'customer_name' => (string) $order->billing_full_name,
            'store_name' => (string) config('shop.store_name', MailBranding::storeName()),
            'order_number' => (string) $order->order_number,
            'payment_due_at' => $order->payment_due_at?->format('M j, Y g:i A')
                ?? 'the deadline shown in your account',
            'payment_timeout_hours' => (string) config('shop.order_payment_timeout_hours', 24),
            'contact_email' => (string) config('shop.contact_email'),
            'contact_phone' => (string) config('shop.contact_phone'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function forOrderStatus(Order $order): array
    {
        return array_merge(self::forOrder($order), [
            'order_status_label' => $order->status->label(),
            'status_message' => self::statusMessage($order->status),
        ]);
    }

    public static function statusMessage(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Processing => 'We have started preparing your items for delivery.',
            OrderStatus::ReadyForDelivery => 'Your order is packed and ready to go out for delivery.',
            OrderStatus::Shipped => 'Your package is on its way to you.',
            OrderStatus::Delivered => 'Your order has been delivered. We hope you enjoy your purchase.',
            default => 'You can follow the latest progress from your account.',
        };
    }
}
