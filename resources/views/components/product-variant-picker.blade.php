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

    $initialSize = old('variant_size');
    $initialColor = old('variant_color');
    $initialHeel = old('variant_heel');
@endphp

<div
    x-data="productVariantPicker(@js($variants), @js($initialSize), @js($initialColor), @js($initialHeel))"
    x-init="syncQuantityInput()"
    class="space-y-5"
>
    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Size</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <template x-for="size in availableSizes" :key="size">
                <button
                    type="button"
                    class="min-w-[3rem] border px-3 py-2 text-sm transition"
                    :class="optionEquals(selectedSize, size) ? 'border-brand-red bg-brand-red text-white' : 'border-neutral-300 bg-white text-brand-black hover:border-brand-red'"
                    @click="selectSize(size)"
                    x-text="size"
                ></button>
            </template>
        </div>
    </div>

    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">
            Color <span class="text-brand-red" aria-hidden="true">*</span>
        </p>
        <div class="mt-3 flex flex-col gap-2">
            <template x-for="color in availableColors" :key="color">
                <button
                    type="button"
                    class="w-full border px-4 py-3 text-center text-sm transition"
                    :class="optionEquals(selectedColor, color) ? 'border-brand-red bg-brand-red text-white' : 'border-neutral-300 bg-white text-brand-black hover:border-brand-red'"
                    @click="selectColor(color)"
                    x-text="color"
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
                    :class="optionEquals(selectedHeel, heel) ? 'border-brand-red bg-brand-red text-white' : 'border-neutral-300 bg-white text-brand-black hover:border-brand-red'"
                    @click="selectHeel(heel)"
                    x-text="heel"
                ></button>
            </template>
        </div>
    </div>

    <input type="hidden" name="variant_size" :value="selectedVariant ? selectedVariant.size : ''">
    <input type="hidden" name="variant_color" :value="selectedVariant ? selectedVariant.color : ''">
    <input type="hidden" name="variant_heel" :value="selectedVariant && selectedVariant.heel_length ? selectedVariant.heel_length : ''">

    <p class="text-sm" x-show="selectionMessage" x-text="selectionMessage" :class="selectedVariant ? 'text-brand-black' : 'text-brand-muted'"></p>
</div>
