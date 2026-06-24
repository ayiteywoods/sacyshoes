<?php

namespace App\Http\Requests\Admin;

use App\Enums\CategoryStatus;
use App\Models\Category;
use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(Category::class)->ignore($categoryId),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(CategoryStatus::class)],
            'show_in_navbar' => ['nullable', 'boolean'],
            'navbar_sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ImageUpload::rules(5120),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $parentId = $this->input('parent_id');
            $category = $this->route('category');

            if (! $parentId) {
                return;
            }

            if ($category && (int) $parentId === $category->id) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');

                return;
            }

            $parent = Category::query()->find($parentId);

            if (! $parent || $parent->parent_id !== null) {
                $validator->errors()->add('parent_id', 'Subcategories can only be added under a main category.');

                return;
            }

            if ($category && $category->children()->exists()) {
                $validator->errors()->add('parent_id', 'Main categories with subcategories cannot be moved under another category.');
            }
        });
    }
}
