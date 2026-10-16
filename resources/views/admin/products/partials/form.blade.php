@php($product = $product ?? null)

<div class="grid min-w-0 gap-4 sm:grid-cols-2">
    <div class="min-w-0 sm:col-span-2">
        <x-form-label :required="true">Name</x-form-label>
        <input type="text" name="name" value="{{ old('name', $product?->name) }}" required class="input-field">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <x-form-label :required="true">SKU</x-form-label>
        <input type="text" name="sku" value="{{ old('sku', $product?->sku) }}" required class="input-field">
        @error('sku')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <x-form-label :required="true">Category</x-form-label>
        <select name="category_id" required class="input-field">
            <option value="">Select category</option>
            @include('partials.category-select-options', [
                'categoryTree' => $categoryTree,
                'selected' => old('category_id', $product?->category_id),
            ])
        </select>
        @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <x-form-label :required="true">Price (GHS)</x-form-label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $product?->price) }}" required class="input-field">
        @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Discount price (GHS)</label>
        <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price', $product?->discount_price) }}" class="input-field">
        @error('discount_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Total stock</label>
        <input
            type="number"
            value="{{ old('quantity', $product?->quantity ?? 0) }}"
            readonly
            class="input-field bg-neutral-50"
        >
        <p class="mt-1 text-xs leading-relaxed text-brand-muted">Calculated automatically from size and color options below.</p>
    </div>

    <div>
        <x-form-label :required="true">Status</x-form-label>
        <select name="status" class="input-field">
            @foreach (\App\Enums\ProductStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $product?->status?->value ?? 'draft') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="min-w-0 rounded border border-neutral-200 bg-neutral-50 p-4 sm:col-span-2">
        <h3 class="text-sm font-semibold">Storefront publish schedule</h3>
        <p class="mt-1 text-xs text-brand-muted">
            Optional. The product appears on the shop when status is Active and this date and time have passed.
            Leave blank to publish immediately.
        </p>

        <div class="mt-4 grid min-w-0 gap-4 sm:grid-cols-2">
            <div>
                <label for="publish_date" class="block text-sm font-medium">Publish date</label>
                <input
                    id="publish_date"
                    type="date"
                    name="publish_date"
                    value="{{ old('publish_date', $product?->published_at?->timezone(config('app.timezone'))->format('Y-m-d')) }}"
                    class="input-field mt-1"
                >
                @error('publish_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="publish_time" class="block text-sm font-medium">Publish time</label>
                <input
                    id="publish_time"
                    type="time"
                    name="publish_time"
                    value="{{ old('publish_time', $product?->published_at?->timezone(config('app.timezone'))->format('H:i')) }}"
                    class="input-field mt-1"
                >
                @error('publish_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        @error('published_at')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        <p class="mt-2 text-xs text-brand-muted">Times use {{ config('app.timezone') }}.</p>
    </div>

    <div class="min-w-0 sm:col-span-2">
        <label class="block text-sm font-medium">Description</label>
        <textarea name="description" rows="5" class="input-field">{{ old('description', $product?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="min-w-0 sm:col-span-2">
        <label class="block text-sm font-medium">Product images</label>
        <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,image/avif,image/heic,image/heif,.jpg,.jpeg,.png,.gif,.webp,.bmp,.avif,.heic,.heif" multiple class="mt-1 w-full max-w-full text-sm">
        <p class="mt-1 text-xs text-brand-muted">Upload JPG, PNG, GIF, WebP, AVIF, or HEIC. Large images are automatically compressed to {{ \App\Support\ImageUpload::targetLabel(4096) }} each.</p>
        @if ($product?->images?->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($product->images as $image)
                    <img src="{{ asset('storage/'.$image->path) }}" alt="" class="h-20 w-20 rounded-lg object-cover">
                @endforeach
            </div>
        @endif
        @error('images')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('images.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="min-w-0 sm:col-span-2" x-data="productVariantForm(@js(old('variants', $product?->variants?->map(fn ($variant) => [
        'id' => $variant->id,
        'size' => $variant->size,
        'color' => $variant->color,
        'heel_length' => $variant->heel_length,
        'quantity' => $variant->quantity,
        'sku' => $variant->sku,
        'is_active' => $variant->is_active,
    ])->values()->all() ?? [[
        'size' => '',
        'color' => '',
        'heel_length' => '',
        'quantity' => 0,
        'sku' => '',
        'is_active' => true,
    ]])))">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <label class="block text-sm font-medium">Size & color options<span class="text-brand-red" aria-hidden="true"> *</span></label>
                <p class="mt-1 text-xs leading-relaxed text-brand-muted">Add the combinations customers can choose from. Size is optional — leave it blank if the product has no sizes. Color is required. Heel length is optional.</p>
            </div>
            <button type="button" class="btn-outline shrink-0 px-3 py-2 text-xs" @click="addRow()">Add option</button>
        </div>

        <div class="mt-4 space-y-3">
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid grid-cols-2 gap-3 border border-neutral-200 p-3 sm:grid-cols-12">
                    <input type="hidden" :name="`variants[${index}][id]`" :value="row.id || ''">

                    <div class="min-w-0 sm:col-span-2">
                        <label class="text-xs text-brand-muted">Size <span class="normal-case">(optional)</span></label>
                        <input type="text" :name="`variants[${index}][size]`" x-model="row.size" list="product-sizes" placeholder="Optional" class="input-field">
                    </div>
                    <div class="min-w-0 sm:col-span-2">
                        <label class="text-xs text-brand-muted">Color <span class="text-brand-red">*</span></label>
                        <input
                            type="text"
                            :name="`variants[${index}][color]`"
                            x-model="row.color"
                            autocomplete="off"
                            placeholder="e.g. Black"
                            required
                            class="input-field"
                        >
                    </div>
                    <div class="min-w-0 sm:col-span-2">
                        <label class="text-xs text-brand-muted">Heel length <span class="normal-case">(optional)</span></label>
                        <input type="text" :name="`variants[${index}][heel_length]`" x-model="row.heel_length" list="product-heel-lengths" placeholder="Optional" class="input-field">
                    </div>
                    <div class="min-w-0 sm:col-span-1">
                        <label class="text-xs text-brand-muted">Qty <span class="text-brand-red">*</span></label>
                        <input type="number" :name="`variants[${index}][quantity]`" x-model="row.quantity" min="0" required class="input-field">
                    </div>
                    <div class="col-span-2 min-w-0 sm:col-span-3">
                        <label class="text-xs text-brand-muted">SKU</label>
                        <input type="text" :name="`variants[${index}][sku]`" x-model="row.sku" placeholder="Auto" class="input-field">
                    </div>
                    <div class="col-span-2 flex items-center justify-between gap-3 sm:col-span-2 sm:flex-col sm:items-start sm:justify-end">
                        <label class="inline-flex items-center gap-2 text-xs text-brand-muted">
                            <input type="hidden" :name="`variants[${index}][is_active]`" :value="row.is_active ? 1 : 0">
                            <input type="checkbox" x-model="row.is_active" class="rounded-none border-neutral-300 text-brand-red focus:ring-brand-red">
                            Active
                        </label>
                        <button type="button" class="text-xs text-brand-red hover:underline" @click="removeRow(index)" x-show="rows.length > 1">Remove</button>
                    </div>
                </div>
            </template>
        </div>

        <datalist id="product-sizes">
            @foreach (config('shop.product_sizes') as $size)
                <option value="{{ $size }}"></option>
            @endforeach
        </datalist>
        <datalist id="product-heel-lengths">
            @foreach (config('shop.product_heel_lengths') as $heelLength)
                <option value="{{ $heelLength }}"></option>
            @endforeach
        </datalist>

        @error('variants')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('variants.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('productVariantForm', (initialRows = []) => ({
                    rows: initialRows.length ? initialRows : [{
                        size: '',
                        color: '',
                        heel_length: '',
                        quantity: 0,
                        sku: '',
                        is_active: true,
                    }],
                    addRow() {
                        this.rows.push({
                            size: '',
                            color: '',
                            heel_length: '',
                            quantity: 0,
                            sku: '',
                            is_active: true,
                        });
                    },
                    removeRow(index) {
                        if (this.rows.length > 1) {
                            this.rows.splice(index, 1);
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
