<?php

namespace App\Http\Requests;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'product_variant_id' => [
                'required',
                'integer',
                Rule::exists(ProductVariant::class, 'id')->where(function ($query) {
                    $query->where('product_id', $this->integer('product_id'))
                        ->where('is_active', true)
                        ->whereColumn('quantity', '>', 'reserved_quantity');
                }),
            ],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_variant_id.required' => 'Please select a size and color.',
            'product_variant_id.exists' => 'The selected size or color is not available.',
            'product_variant_id.integer' => 'Please select a size and color.',
        ];
    }
}
