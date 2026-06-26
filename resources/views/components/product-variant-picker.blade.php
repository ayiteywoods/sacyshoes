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

    $allSizes = [];
    $seenSizes = [];

    foreach ($variants as $variant) {
        $size = (string) ($variant['size'] ?? '');
        $key = strtolower(trim($size));

        if ($key === '' || isset($seenSizes[$key])) {
            continue;
        }

        $seenSizes[$key] = true;
        $allSizes[] = $size;
    }

    usort($allSizes, function (string $left, string $right): int {
        if (is_numeric($left) && is_numeric($right)) {
            return (float) $left <=> (float) $right;
        }

        return strnatcasecmp($left, $right);
    });

    $initialSize = old('variant_size');
    $initialColor = old('variant_color');
    $initialHeel = old('variant_heel');
@endphp

<div
    x-data="productVariantPicker(@js($variants), @js($allSizes), @js($initialSize), @js($initialColor), @js($initialHeel))"
    x-init="init()"
    class="space-y-5"
>
    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Size</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @forelse ($allSizes as $index => $size)
                @php
                    $sizeInStock = $variants->contains(
                        fn (array $variant) => strcasecmp(trim((string) $variant['size']), trim($size)) === 0
                            && $variant['quantity'] > 0,
                    );
                @endphp
                <button
                    type="button"
                    class="variant-size-option min-w-[3rem] border px-3 py-2 text-sm transition"
                    :class="sizeButtonClass(sizeOptions[{{ $index }}])"
                    :disabled="! isSizeInStock(sizeOptions[{{ $index }}])"
                    :aria-disabled="! isSizeInStock(sizeOptions[{{ $index }}])"
                    @click.prevent="selectSize(sizeOptions[{{ $index }}])"
                    @unless ($sizeInStock) disabled @endunless
                >{{ $size }}</button>
            @empty
                <p class="text-sm text-brand-muted">No sizes configured for this product.</p>
            @endforelse
        </div>
    </div>

    <div>
        <div class="flex items-center gap-4">
            <label for="variant-color" class="shrink-0 text-sm lowercase text-brand-muted">color</label>
            <select
                id="variant-color"
                class="input-field mt-0 w-full max-w-[9rem]"
                x-model="selectedColor"
                @change="onColorChange()"
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
                    @click.prevent="adjustQuantity(-1)"
                    :disabled="!canChangeQuantity || quantity <= 1"
                    aria-label="Decrease quantity"
                >−</button>
                <input
                    id="quantity"
                    type="number"
                    name="quantity"
                    min="1"
                    x-model.number="quantity"
                    :max="quantityCap"
                    class="input-field mt-0 h-10 w-14 rounded-none border-x-0 text-center [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    :disabled="!canChangeQuantity"
                    readonly
                >
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center border border-neutral-300 bg-white text-lg leading-none transition hover:border-brand-red disabled:cursor-not-allowed disabled:opacity-50"
                    @click.prevent="adjustQuantity(1)"
                    :disabled="!canChangeQuantity || quantity >= quantityCap"
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
