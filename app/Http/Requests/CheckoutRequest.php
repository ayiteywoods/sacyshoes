<?php

namespace App\Http\Requests;

use App\Models\ShippingOption;
use App\Models\ShippingRegion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_full_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:30'],
            'shipping_email' => ['required', 'email', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_country' => ['required', 'string', 'max:255'],
            'shipping_region_id' => ['required', 'integer', 'exists:shipping_regions,id'],
            'shipping_option_id' => ['nullable', 'integer', 'exists:shipping_options,id'],
            'customer_comment' => ['nullable', 'string', 'max:1000'],
            'billing_full_name' => ['required', 'string', 'max:255'],
            'billing_phone' => ['required', 'string', 'max:30'],
            'billing_email' => ['required', 'email', 'max:255'],
            'billing_address' => ['required', 'string', 'max:500'],
            'billing_city' => ['required', 'string', 'max:255'],
            'billing_country' => ['required', 'string', 'max:255'],
            'save_address' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! auth()->check()) {
            $this->merge(['save_address' => false]);
        }

        $billingSameAsShipping = $this->has('billing_same_as_shipping')
            ? $this->boolean('billing_same_as_shipping')
            : ! $this->filled('billing_full_name');

        if ($billingSameAsShipping) {
            $this->merge([
                'billing_same_as_shipping' => true,
                'billing_full_name' => $this->input('shipping_full_name'),
                'billing_phone' => $this->input('shipping_phone'),
                'billing_email' => $this->input('shipping_email'),
                'billing_address' => $this->input('shipping_address'),
                'billing_city' => $this->input('shipping_city'),
                'billing_country' => $this->input('shipping_country'),
            ]);
        }

        $regionId = $this->integer('shipping_region_id');
        if ($regionId && ShippingRegion::query()->find($regionId)?->is_accra) {
            $this->merge(['shipping_option_id' => null]);
        } else {
            $this->merge(['customer_comment' => null]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $regionId = $this->integer('shipping_region_id');
            if (! $regionId) {
                return;
            }

            $region = ShippingRegion::query()->find($regionId);
            if (! $region) {
                return;
            }

            $optionId = $this->input('shipping_option_id');

            if (! $region->is_accra && ! $optionId) {
                $validator->errors()->add('shipping_option_id', 'Please select a delivery option for your region.');

                return;
            }

            if ($optionId) {
                $option = ShippingOption::query()
                    ->whereKey($optionId)
                    ->where('shipping_region_id', $regionId)
                    ->where('is_active', true)
                    ->first();

                if (! $option) {
                    $validator->errors()->add('shipping_option_id', 'Selected delivery option is not available for the chosen region.');
                }
            }
        });
    }
}
