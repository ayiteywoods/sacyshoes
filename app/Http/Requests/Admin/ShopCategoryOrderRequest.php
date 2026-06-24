<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShopCategoryOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'integer', 'exists:categories,id'],
            'categories.*.shop_sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<int, array{id: int, shop_sort_order: int}>
     */
    public function orderedCategories(): array
    {
        return collect($this->input('categories', []))
            ->map(fn (array $row) => [
                'id' => (int) $row['id'],
                'shop_sort_order' => (int) ($row['shop_sort_order'] ?? 0),
            ])
            ->all();
    }
}
