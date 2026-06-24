<?php

namespace App\Http\Requests\Admin;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_phone_alt' => ['nullable', 'string', 'max:30'],
            'contact_address' => ['required', 'string', 'max:500'],
            'contact_website' => ['nullable', 'string', 'max:255'],
            'contact_page_email' => ['required', 'email', 'max:255'],
            'contact_page_phone' => ['nullable', 'string', 'max:30'],
            'contact_page_phone_alt' => ['nullable', 'string', 'max:30'],
            'contact_page_address' => ['nullable', 'string', 'max:500'],
            'contact_page_hours_days' => ['nullable', 'string', 'max:100'],
            'contact_page_hours_time' => ['nullable', 'string', 'max:100'],
            'contact_page_hours_note' => ['nullable', 'string', 'max:255'],
            'about_hero_description' => ['nullable', 'string', 'max:1000'],
            'footer_tagline' => ['required', 'string', 'max:255'],
            'footer_subline' => ['required', 'string', 'max:255'],
            'delivery_shipping_note' => ['required', 'string', 'max:255'],
            'delivery_info_accra' => ['required', 'string', 'max:1000'],
            'about_image' => ImageUpload::rules(5120),
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_tiktok' => ['nullable', 'string', 'max:255'],
            'social_x' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],
            'social_whatsapp' => ['nullable', 'string', 'max:30'],
        ];
    }
}
