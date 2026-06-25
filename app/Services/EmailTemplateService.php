<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EmailTemplateService
{
    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return [
            EmailTemplate::SLUG_WELCOME,
            EmailTemplate::SLUG_ORDER_CREATED,
            EmailTemplate::SLUG_PAYMENT_RECEIVED,
            EmailTemplate::SLUG_ORDER_STATUS,
            EmailTemplate::SLUG_ORDER_CANCELLED,
        ];
    }

    public function find(string $slug): ?EmailTemplate
    {
        return EmailTemplate::query()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return array{subject: string, body: string, placeholders: list<string>}|null
     */
    protected function cachedData(string $slug): ?array
    {
        $data = Cache::remember("email_template.{$slug}", 3600, function () use ($slug) {
            $template = $this->find($slug);

            if ($template === null) {
                return null;
            }

            return [
                'subject' => $template->subject,
                'body' => $template->body,
                'placeholders' => $template->placeholderList(),
            ];
        });

        return is_array($data) ? $data : null;
    }

    /**
     * @param  array<string, string>  $replacements
     */
    public function subject(string $slug, array $replacements = []): string
    {
        $data = $this->cachedData($slug);
        $subject = $data['subject'] ?? $this->defaultSubject($slug);

        return $this->replace($subject, $replacements);
    }

    /**
     * @param  array<string, string>  $replacements
     */
    public function renderBodyHtml(string $slug, array $replacements = []): string
    {
        $data = $this->cachedData($slug);
        $body = $data['body'] ?? $this->defaultBody($slug);

        return Str::markdown($this->replace($body, $replacements));
    }

    /**
     * @return list<string>
     */
    public function placeholders(string $slug): array
    {
        $data = $this->cachedData($slug);

        return $data['placeholders'] ?? $this->defaultPlaceholders($slug);
    }

    public function clearCache(?string $slug = null): void
    {
        if ($slug !== null) {
            Cache::forget("email_template.{$slug}");

            return;
        }

        foreach (self::slugs() as $item) {
            Cache::forget("email_template.{$item}");
        }
    }

    public function syncDefaultContent(string $slug): void
    {
        EmailTemplate::query()
            ->where('slug', $slug)
            ->update([
                'subject' => $this->defaultSubject($slug),
                'body' => $this->defaultBody($slug),
            ]);

        $this->clearCache($slug);
    }

    /**
     * @param  array<string, string>  $replacements
     */
    protected function replace(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $text = str_replace('{{'.$key.'}}', (string) $value, $text);
        }

        return $text;
    }

    protected function defaultSubject(string $slug): string
    {
        return match ($slug) {
            EmailTemplate::SLUG_WELCOME => 'Welcome to {{store_name}}',
            EmailTemplate::SLUG_ORDER_CREATED => 'Order {{order_number}} received',
            EmailTemplate::SLUG_PAYMENT_RECEIVED => 'Good things are heading your way! — {{store_name}}',
            EmailTemplate::SLUG_ORDER_STATUS => 'Delivery update for order {{order_number}}',
            EmailTemplate::SLUG_ORDER_CANCELLED => 'Order {{order_number}} cancelled',
            default => 'Message from {{store_name}}',
        };
    }

    protected function defaultBody(string $slug): string
    {
        return match ($slug) {
            EmailTemplate::SLUG_WELCOME => <<<'MD'
# Welcome to {{store_name}}

Hi {{first_name}},

Thanks for creating your account. You can now shop our latest footwear, track orders, and manage your profile anytime.
MD,
            EmailTemplate::SLUG_ORDER_CREATED => <<<'MD'
# Order Received

Hi {{customer_name}},

Thank you for shopping with {{store_name}}. We received your order and it is waiting for payment.

Complete payment by **{{payment_due_at}}** to confirm your order. If payment is not received within {{payment_timeout_hours}} hours, the order will be cancelled and items returned to stock.
MD,
            EmailTemplate::SLUG_PAYMENT_RECEIVED => <<<'MD'
# Good things are heading your way!

Hi {{customer_name}},

We have finished processing your order. Here's a reminder of what you've ordered.

Your invoice is attached to this email as a PDF — you can open it directly from your inbox without clicking any links.

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
MD,
            default => '',
        };
    }

    /**
     * @return list<string>
     */
    protected function defaultPlaceholders(string $slug): array
    {
        return match ($slug) {
            EmailTemplate::SLUG_WELCOME => ['first_name', 'customer_name', 'store_name'],
            EmailTemplate::SLUG_ORDER_CREATED => [
                'customer_name', 'store_name', 'order_number', 'payment_due_at', 'payment_timeout_hours',
                'contact_email', 'contact_phone',
            ],
            EmailTemplate::SLUG_PAYMENT_RECEIVED => [
                'customer_name', 'store_name', 'order_number', 'contact_email', 'contact_phone',
            ],
            EmailTemplate::SLUG_ORDER_STATUS => [
                'customer_name', 'store_name', 'order_number', 'order_status_label', 'status_message',
            ],
            EmailTemplate::SLUG_ORDER_CANCELLED => [
                'customer_name', 'store_name', 'order_number', 'payment_timeout_hours',
                'contact_email', 'contact_phone',
            ],
            default => [],
        };
    }
}
