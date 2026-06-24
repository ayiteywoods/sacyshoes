@props(['product'])

@php
    $variants = $product->variants
        ->map(fn ($variant) => [
            'id' => $variant->id,
            'size' => $variant->size,
            'color' => $variant->color,
            'heel_length' => filled($variant->heel_length) ? $variant->heel_length : null,
            'quantity' => $variant->availableQuantity(),
            'sku' => $variant->sku,
        ])
        ->values();

    $colorSwatches = config('shop.product_color_swatches');
@endphp

<div
    x-data="productVariantPicker(@js($variants), @js($colorSwatches))"
    class="space-y-5"
>
    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Size</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <template x-for="size in availableSizes" :key="size">
                <button
                    type="button"
                    class="min-w-[3rem] border px-3 py-2 text-sm transition"
                    :class="selectedSize === size ? 'border-brand-red bg-brand-red text-white' : 'border-neutral-300 bg-white text-brand-black hover:border-brand-red'"
                    @click="selectSize(size)"
                    x-text="size"
                ></button>
            </template>
        </div>
    </div>

    <div>
        <div class="flex items-center gap-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Color</p>
            <span
                x-show="selectedColor"
                x-text="selectedColor"
                class="text-xs font-medium normal-case tracking-normal text-brand-black"
            ></span>
        </div>
        <div class="mt-3 flex flex-wrap gap-3">
            <template x-for="color in availableColors" :key="color">
                <button
                    type="button"
                    class="color-swatch h-10 w-10 rounded-full border-2 transition hover:scale-105"
                    :class="selectedColor === color ? 'border-brand-red ring-2 ring-brand-red ring-offset-2' : 'border-neutral-300'"
                    :style="{ backgroundColor: colorSwatch(color) }"
                    :title="color"
                    :aria-label="color"
                    :aria-pressed="selectedColor === color"
                    @click="selectColor(color)"
                ></button>
            </template>
        </div>
    </div>

    <div x-show="showHeelSection" x-cloak>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">
            Heel length <span class="normal-case text-brand-muted">(optional)</span>
        </p>
        <div class="mt-3 flex flex-wrap gap-2">
            <template x-for="heel in availableHeels" :key="heel">
                <button
                    type="button"
                    class="border px-3 py-2 text-sm transition"
                    :class="selectedHeel === heel ? 'border-brand-red bg-brand-red text-white' : 'border-neutral-300 bg-white text-brand-black hover:border-brand-red'"
                    @click="selectHeel(heel)"
                    x-text="heel"
                ></button>
            </template>
        </div>
    </div>

    <input type="hidden" name="product_variant_id" x-bind:value="selectedVariant ? selectedVariant.id : ''">

    <p class="text-sm" x-show="selectionMessage" x-text="selectionMessage" :class="selectedVariant ? 'text-brand-black' : 'text-brand-muted'"></p>

    @error('product_variant_id')
        <p class="text-sm text-brand-red">{{ $message }}</p>
    @enderror
</div>
