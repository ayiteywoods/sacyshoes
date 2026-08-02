<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'variant_size' => ['nullable', 'string', 'max:50'],
            'variant_color' => ['required', 'string', 'max:50'],
            'variant_heel' => ['nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $product = Product::query()->with('variants')->find($this->integer('product_id'));

            if (! $product) {
                return;
            }

            if ($product->requiresSizeSelection() && blank($this->input('variant_size'))) {
                $validator->errors()->add('variant_size', 'Please select a size.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'variant_size.required' => 'Please select a size.',
            'variant_color.required' => 'Please select a color.',
        ];
    }
}
