<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\EmailTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

class EmailPreviewData
{
    /**
     * @return array<string, string>
     */
    public static function replacementsFor(string $slug): array
    {
        $storeName = MailBranding::storeName();

        return match ($slug) {
            EmailTemplate::SLUG_WELCOME => [
                'first_name' => 'Ama',
                'customer_name' => 'Ama Mensah',
                'store_name' => $storeName,
            ],
            EmailTemplate::SLUG_ORDER_CREATED,
            EmailTemplate::SLUG_PAYMENT_RECEIVED,
            EmailTemplate::SLUG_ORDER_STATUS,
            EmailTemplate::SLUG_ORDER_CANCELLED => array_merge(
                self::orderReplacements(),
                $slug === EmailTemplate::SLUG_ORDER_STATUS ? self::statusReplacements() : [],
            ),
            default => ['store_name' => $storeName],
        };
    }

    public static function sampleUser(): User
    {
        return new User([
            'first_name' => 'Ama',
            'last_name' => 'Mensah',
            'name' => 'Ama Mensah',
            'email' => 'customer@example.com',
        ]);
    }

    public static function sampleOrder(): Order
    {
        $order = new Order([
            'order_number' => 'SS-240617-001',
            'billing_full_name' => 'Ama Mensah',
            'billing_email' => 'customer@example.com',
            'status' => OrderStatus::PendingPayment,
            'subtotal' => 320.00,
            'shipping_total' => 25.00,
            'discount_total' => 0,
            'total' => 345.00,
            'payment_due_at' => Carbon::now()->addHours(24),
        ]);

        $order->setRelation('items', collect());

        return $order;
    }

    /**
     * @return array<string, string>
     */
    protected static function orderReplacements(): array
    {
        return [
            'customer_name' => 'Ama Mensah',
            'store_name' => MailBranding::storeName(),
            'order_number' => 'SS-240617-001',
            'payment_due_at' => Carbon::now()->addHours(24)->format('M j, Y g:i A'),
            'payment_timeout_hours' => (string) config('shop.order_payment_timeout_hours', 24),
            'contact_email' => (string) config('shop.contact_email'),
            'contact_phone' => (string) config('shop.contact_phone'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function statusReplacements(): array
    {
        return [
            'order_status_label' => OrderStatus::Shipped->label(),
            'status_message' => 'Your package is on its way to you.',
        ];
    }
}
