<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NavbarCategoryRequest extends FormRequest
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
            'categories.*.show_in_navbar' => ['nullable', 'boolean'],
            'categories.*.navbar_sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<int, array{id: int, show_in_navbar: bool, navbar_sort_order: int}>
     */
    public function navbarCategories(): array
    {
        return collect($this->input('categories', []))
            ->map(fn (array $row) => [
                'id' => (int) $row['id'],
                'show_in_navbar' => filter_var($row['show_in_navbar'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'navbar_sort_order' => (int) ($row['navbar_sort_order'] ?? 0),
            ])
            ->all();
    }
}
