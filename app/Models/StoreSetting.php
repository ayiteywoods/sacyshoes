<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'contact_email',
        'contact_phone',
        'contact_phone_alt',
        'contact_address',
        'contact_website',
        'contact_page_email',
        'contact_page_phone',
        'contact_page_phone_alt',
        'contact_page_address',
        'contact_page_hours_days',
        'contact_page_hours_time',
        'contact_page_hours_note',
        'about_image_path',
        'about_hero_description',
        'footer_tagline',
        'footer_subline',
        'delivery_shipping_note',
        'delivery_info_accra',
        'social_facebook',
        'social_instagram',
        'social_tiktok',
        'social_x',
        'social_youtube',
        'social_whatsapp',
        'maintenance_mode',
        'maintenance_message',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function aboutImageUrl(): string
    {
        if (! $this->about_image_path) {
            return asset('images/brand/shoes.jpg');
        }

        if (str_starts_with($this->about_image_path, 'images/') || str_starts_with($this->about_image_path, 'http')) {
            return asset($this->about_image_path);
        }

        return Storage::disk('public')->url($this->about_image_path);
    }

    public function contactPhoneAlt(): ?string
    {
        return filled($this->contact_phone_alt) ? $this->contact_phone_alt : null;
    }

    /**
     * @return list<string>
     */
    public function invoiceContactPhones(): array
    {
        $primary = filled($this->contact_phone)
            ? $this->contact_phone
            : (string) config('shop.contact_phone');

        $phones = [];

        if (filled($primary)) {
            $phones[] = trim($primary);
        }

        $alt = $this->contactPhoneAlt();

        if ($alt && self::normalizePhoneDigits($alt) !== self::normalizePhoneDigits($primary)) {
            $phones[] = trim($alt);
        }

        return $phones;
    }

    public function contactPageEmail(): string
    {
        return filled($this->contact_page_email)
            ? $this->contact_page_email
            : (string) config('shop.contact_page_email', 'support@sacyshoes.com');
    }

    public function contactPageAddress(): string
    {
        if (filled($this->contact_page_address)) {
            return $this->contact_page_address;
        }

        return filled($this->contact_address)
            ? $this->contact_address
            : (string) config('shop.contact_address');
    }

    public function contactPagePhoneAlt(): ?string
    {
        if (filled($this->contact_page_phone_alt)) {
            return $this->contact_page_phone_alt;
        }

        return $this->contactPhoneAlt();
    }

    /**
     * @return list<string>
     */
    public function contactPagePhones(): array
    {
        $primary = filled($this->contact_page_phone)
            ? $this->contact_page_phone
            : (filled($this->contact_phone)
                ? $this->contact_phone
                : (string) config('shop.contact_phone'));

        $phones = [];

        if (filled($primary)) {
            $phones[] = trim($primary);
        }

        $alt = $this->contactPagePhoneAlt();

        if ($alt && self::normalizePhoneDigits($alt) !== self::normalizePhoneDigits($primary)) {
            $phones[] = trim($alt);
        }

        return $phones;
    }

    public function contactPageHoursDays(): string
    {
        return $this->contact_page_hours_days ?: 'Monday – Saturday';
    }

    public function contactPageHoursTime(): string
    {
        return $this->contact_page_hours_time ?: '9:00 AM – 6:00 PM';
    }

    public function contactPageHoursNote(): string
    {
        return $this->contact_page_hours_note ?: 'Closed on Sundays and public holidays.';
    }

    public static function normalizePhoneDigits(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    public function phoneTelUri(string $phone): string
    {
        $digits = self::normalizePhoneDigits($phone);

        if ($digits === '') {
            return '#';
        }

        if (str_starts_with($digits, '233')) {
            return 'tel:+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            return 'tel:+233'.substr($digits, 1);
        }

        return 'tel:+'.$digits;
    }

    public function aboutHeroDescription(): string
    {
        return $this->about_hero_description
            ?: config('shop.store_name').' is a Ghanaian footwear brand built on quality, comfort, and style — delivering premium shoes for every occasion, nationwide.';
    }

    public function footerTagline(): string
    {
        return $this->footer_tagline ?: 'Premium footwear curated for every occasion.';
    }

    public function footerSubline(): string
    {
        return $this->footer_subline ?: 'Quality shoes delivered across Ghana.';
    }

    public function isMaintenanceModeEnabled(): bool
    {
        return (bool) $this->maintenance_mode;
    }

    public function maintenanceMessage(): string
    {
        return $this->maintenance_message
            ?: 'We are currently performing scheduled maintenance. Please check back soon.';
    }

    public function deliveryShippingNote(): string
    {
        return $this->delivery_shipping_note
            ?: config('shop.delivery_info.shipping_note', 'Shipping calculated at checkout.');
    }

    /**
     * @return list<array{icon: string, text: string}>
     */
    public function deliveryInfoItems(): array
    {
        $defaults = config('shop.delivery_info.items', []);

        $items = [];

        $accra = $this->delivery_info_accra ?: ($defaults[0]['text'] ?? '');
        if (filled($accra)) {
            $items[] = [
                'icon' => $defaults[0]['icon'] ?? 'truck',
                'text' => $accra,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{platform: string, label: string, url: string}>
     */
    public function socialLinks(): array
    {
        $platforms = [
            'facebook' => ['column' => 'social_facebook', 'label' => 'Facebook'],
            'instagram' => ['column' => 'social_instagram', 'label' => 'Instagram'],
            'tiktok' => ['column' => 'social_tiktok', 'label' => 'TikTok'],
            'x' => ['column' => 'social_x', 'label' => 'X'],
            'youtube' => ['column' => 'social_youtube', 'label' => 'YouTube'],
            'whatsapp' => ['column' => 'social_whatsapp', 'label' => 'WhatsApp'],
        ];

        $links = [];

        foreach ($platforms as $platform => $meta) {
            $value = trim((string) $this->{$meta['column']});

            if ($value === '') {
                continue;
            }

            $links[] = [
                'platform' => $platform,
                'label' => $meta['label'],
                'url' => $this->normalizeSocialUrl($platform, $value),
            ];
        }

        return $links;
    }

    protected function normalizeSocialUrl(string $platform, string $value): string
    {
        if ($platform === 'whatsapp') {
            $digits = preg_replace('/\D+/', '', $value) ?? '';

            if ($digits !== '') {
                return 'https://wa.me/'.$digits;
            }
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return 'https://'.ltrim($value, '/');
    }
}
