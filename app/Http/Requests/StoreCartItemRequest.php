<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'variant_size' => ['required', 'string', 'max:50'],
            'variant_color' => ['required', 'string', 'max:50'],
            'variant_heel' => ['nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'variant_size.required' => 'Please select a size and color.',
            'variant_color.required' => 'Please select a size and color.',
        ];
    }
}
