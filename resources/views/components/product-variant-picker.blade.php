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
        <div class="flex items-center gap-4">
            <label for="variant-color" class="shrink-0 text-sm lowercase text-brand-muted">color</label>
            <select
                id="variant-color"
                class="input-field mt-0 w-full max-w-[9rem]"
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

    <div class="flex flex-col gap-4 pt-2">
        <div class="flex items-center gap-3">
            <span class="text-sm uppercase tracking-wide text-brand-muted">Qty</span>
            <div class="flex items-stretch">
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center border border-neutral-300 bg-white text-lg leading-none transition hover:border-brand-red disabled:cursor-not-allowed disabled:opacity-50"
                    @click="adjustQuantity(-1)"
                    :disabled="!selectedVariant || quantity <= 1"
                    aria-label="Decrease quantity"
                >−</button>
                <input
                    id="quantity"
                    type="number"
                    name="quantity"
                    min="1"
                    max="1"
                    value="1"
                    class="input-field mt-0 h-10 w-14 rounded-none border-x-0 text-center [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    disabled
                    readonly
                >
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center border border-neutral-300 bg-white text-lg leading-none transition hover:border-brand-red disabled:cursor-not-allowed disabled:opacity-50"
                    @click="adjustQuantity(1)"
                    :disabled="!selectedVariant || quantity >= maxQuantity"
                    aria-label="Increase quantity"
                >+</button>
            </div>
        </div>

        <button
            id="add-to-cart"
            type="submit"
            data-out-of-stock="{{ $product->isInStock() ? 'false' : 'true' }}"
            class="btn-primary w-full py-3 disabled:cursor-not-allowed disabled:opacity-50"
            @disabled(! $product->isInStock())
        >
            {{ $product->isInStock() ? 'Add To Cart' : 'Out of Stock' }}
        </button>
    </div>
</div>
