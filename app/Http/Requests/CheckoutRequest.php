<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'billing_full_name' => ['required', 'string', 'max:255'],
            'billing_phone' => ['required', 'string', 'max:30'],
            'billing_email' => ['required', 'email', 'max:255'],
            'billing_address' => ['required', 'string', 'max:500'],
            'billing_city' => ['required', 'string', 'max:255'],
            'billing_country' => ['required', 'string', 'max:255'],
            'save_address' => ['nullable', 'boolean'],
        ];
    }
}
