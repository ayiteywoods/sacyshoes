<?php

namespace App\Http\Requests\Admin;

use App\Models\ShippingRegion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingRegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $regionId = $this->route('shipping_region')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(ShippingRegion::class, 'name')->ignore($regionId)],
            'is_accra' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', 'integer', 'exists:shipping_options,id'],
            'options.*.name' => ['required_with:options', 'string', 'max:255'],
            'options.*.price' => ['required_with:options', 'numeric', 'min:0'],
            'options.*.description' => ['nullable', 'string', 'max:1000'],
            'options.*.is_active' => ['nullable', 'boolean'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'options.*.name.required_with' => 'Each shipping option must have a name.',
            'options.*.price.required_with' => 'Each shipping option must have a price.',
        ];
    }
}
