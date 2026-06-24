<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = app(EmailTemplateService::class);

        $definitions = [
            [
                'slug' => EmailTemplate::SLUG_WELCOME,
                'name' => 'Welcome email',
                'description' => 'Sent when a customer creates an account.',
                'sort_order' => 1,
            ],
            [
                'slug' => EmailTemplate::SLUG_ORDER_CREATED,
                'name' => 'Order received',
                'description' => 'Sent when a new order is placed and awaiting payment.',
                'sort_order' => 2,
            ],
            [
                'slug' => EmailTemplate::SLUG_PAYMENT_RECEIVED,
                'name' => 'Payment confirmed',
                'description' => 'Sent when payment is successful. Includes the PDF invoice attachment.',
                'sort_order' => 3,
            ],
            [
                'slug' => EmailTemplate::SLUG_ORDER_STATUS,
                'name' => 'Delivery update',
                'description' => 'Sent when an order moves to processing, ready for delivery, shipped, or delivered.',
                'sort_order' => 4,
            ],
            [
                'slug' => EmailTemplate::SLUG_ORDER_CANCELLED,
                'name' => 'Order cancelled',
                'description' => 'Sent when an unpaid order is automatically cancelled.',
                'sort_order' => 5,
            ],
        ];

        foreach ($definitions as $definition) {
            EmailTemplate::query()->firstOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'subject' => $templates->subject($definition['slug']),
                    'body' => $this->bodyFor($definition['slug']),
                    'placeholders' => $templates->placeholders($definition['slug']),
                    'sort_order' => $definition['sort_order'],
                ]
            );
        }

        $templates->clearCache();
    }

    protected function bodyFor(string $slug): string
    {
        return match ($slug) {
            EmailTemplate::SLUG_WELCOME => <<<'MD'
# Welcome to {{store_name}}

Hi {{first_name}},

Thanks for creating your account. You can now shop our latest footwear, track orders, and manage your profile anytime.

We are glad to have you with us — step into style with every order.
MD,
            EmailTemplate::SLUG_ORDER_CREATED => <<<'MD'
# Order Received

Hi {{customer_name}},

Thank you for shopping with {{store_name}}. We received your order and it is waiting for payment.

Complete payment by **{{payment_due_at}}** to confirm your order. If payment is not received within {{payment_timeout_hours}} hours, the order will be cancelled and items returned to stock.

If you have questions, contact us at {{contact_email}} or {{contact_phone}}.
MD,
            EmailTemplate::SLUG_PAYMENT_RECEIVED => <<<'MD'
# Payment Confirmed

Hi {{customer_name}},

Your payment for order **{{order_number}}** was successful. Your invoice is attached to this email as a PDF.

If you have questions, contact us at {{contact_email}} or {{contact_phone}}.
MD,
            EmailTemplate::SLUG_ORDER_STATUS => <<<'MD'
# Delivery Update

Hi {{customer_name}},

Your order **{{order_number}}** has moved to the next stage:

**{{order_status_label}}**

{{status_message}}
MD,
            EmailTemplate::SLUG_ORDER_CANCELLED => <<<'MD'
# Order Cancelled

Hi {{customer_name}},

Your order **{{order_number}}** was cancelled because payment was not received within {{payment_timeout_hours}} hours.

The items have been returned to stock. You can place a new order anytime from our shop.

If you believe this is a mistake or you already paid, contact us at {{contact_email}} or {{contact_phone}}.
MD,
            default => '',
        };
    }
}
