<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Support\ImageUpload;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(Product::class)->ignore($productId),
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique(Product::class)->ignore($productId),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'publish_date' => ['nullable', 'date'],
            'publish_time' => ['nullable', 'date_format:H:i'],
            'published_at' => ['nullable', 'date'],
            'images' => ['nullable', 'array'],
            'images.*' => [
                'required',
                ImageUpload::typesRule(),
            ],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.size' => ['required', 'string', 'max:50'],
            'variants.*.color' => ['required', 'string', 'max:50'],
            'variants.*.heel_length' => ['nullable', 'string', 'max:50'],
            'variants.*.quantity' => ['required', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $date = $this->input('publish_date');
        $time = $this->input('publish_time');

        if (filled($date) && filled($time)) {
            $this->merge([
                'published_at' => Carbon::parse("{$date} {$time}", config('app.timezone'))->toDateTimeString(),
            ]);
        } elseif (filled($date)) {
            $this->merge([
                'published_at' => Carbon::parse("{$date} 00:00", config('app.timezone'))->toDateTimeString(),
            ]);
        } else {
            $this->merge(['published_at' => null]);
        }

        if (! $this->hasFile('images')) {
            $this->request->remove('images');

            return;
        }

        $images = collect(Arr::wrap($this->file('images')))
            ->filter(fn ($file) => $file instanceof UploadedFile && $file->isValid())
            ->values()
            ->all();

        if ($images === []) {
            $this->files->remove('images');
            $this->request->remove('images');

            return;
        }

        $this->files->set('images', $images);
    }

    /**
     * @return list<UploadedFile>
     */
    public function uploadedImages(): array
    {
        return collect(Arr::wrap($this->file('images', [])))
            ->filter(fn ($file) => $file instanceof UploadedFile && $file->isValid())
            ->values()
            ->all();
    }

    public function messages(): array
    {
        return [
            'images.*.required' => 'Each selected file must be a valid image upload.',
            'images.*.extensions' => 'Product images must be JPG, PNG, GIF, WebP, or HEIC.',
            'images.*.max' => 'Each product image must not be larger than 20 MB before compression.',
        ];
    }
}
