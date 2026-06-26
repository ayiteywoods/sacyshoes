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
    $colorMap = config('shop.product_color_map', []);
@endphp

<div
    x-data="productVariantPicker(@js($variants), @js($colorMap), @js($initialSize), @js($initialColor), @js($initialHeel))"
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
        <div class="flex items-center gap-4">
            <label for="variant-color" class="shrink-0 text-sm lowercase text-brand-muted">color</label>
            <div class="flex flex-1 items-center gap-3">
                <span
                    class="h-8 w-8 shrink-0 border border-neutral-300"
                    :style="{ backgroundColor: colorCss(selectedColor) }"
                    aria-hidden="true"
                ></span>
                <select
                    id="variant-color"
                    class="input-field mt-0 flex-1"
                    :value="selectedColor ?? ''"
                    @change="selectColor($event.target.value || null)"
                >
                    <option value="">Select color</option>
                    <template x-for="color in availableColors" :key="color">
                        <option :value="color" x-text="color"></option>
                    </template>
                </select>
            </div>
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
