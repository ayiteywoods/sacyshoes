<?php

namespace App\Services;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;

class StoreSettingService
{
    public function applyToConfig(): void
    {
        if (! Schema::hasTable('store_settings')) {
            return;
        }

        $settings = StoreSetting::query()->first();

        if (! $settings) {
            return;
        }

        $map = [
            'store_name' => 'shop.store_name',
            'contact_email' => 'shop.contact_email',
            'contact_phone' => 'shop.contact_phone',
            'contact_address' => 'shop.contact_address',
            'contact_website' => 'shop.contact_website',
        ];

        foreach ($map as $column => $configKey) {
            $value = $settings->{$column};

            if (filled($value)) {
                config([$configKey => $value]);
            }
        }

        if ($settings->contactPhoneAlt()) {
            config(['shop.contact_phone_alt' => $settings->contactPhoneAlt()]);
        }

        config([
            'shop.delivery_info.shipping_note' => $settings->deliveryShippingNote(),
            'shop.delivery_info.items' => $settings->deliveryInfoItems(),
            'shop.maintenance_mode' => $settings->isMaintenanceModeEnabled(),
            'shop.maintenance_message' => $settings->maintenanceMessage(),
        ]);
    }

    public function current(): StoreSetting
    {
        $settings = StoreSetting::current();

        $settings->fill([
            'store_name' => $settings->store_name ?: config('shop.store_name'),
            'contact_email' => $settings->contact_email ?: config('shop.contact_email'),
            'contact_phone' => $settings->contact_phone ?: config('shop.contact_phone'),
            'contact_address' => $settings->contact_address ?: config('shop.contact_address'),
            'contact_website' => $settings->contact_website ?: config('shop.contact_website'),
            'contact_page_email' => $settings->contact_page_email ?: config('shop.contact_page_email'),
            'contact_page_hours_days' => $settings->contact_page_hours_days ?: 'Monday – Saturday',
            'contact_page_hours_time' => $settings->contact_page_hours_time ?: '9:00 AM – 6:00 PM',
            'contact_page_hours_note' => $settings->contact_page_hours_note ?: 'Closed on Sundays and public holidays.',
            'delivery_shipping_note' => $settings->delivery_shipping_note ?: config('shop.delivery_info.shipping_note'),
            'delivery_info_accra' => $settings->delivery_info_accra ?: data_get(config('shop.delivery_info.items'), '0.text'),
            'footer_tagline' => $settings->footerTagline(),
            'footer_subline' => $settings->footerSubline(),
        ]);

        return $settings;
    }
}
