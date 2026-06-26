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

    $pickerConfig = [
        'variants' => $variants,
        'initialSize' => old('variant_size'),
        'initialColor' => old('variant_color'),
        'initialHeel' => old('variant_heel'),
    ];
@endphp

<div
    class="product-variant-picker space-y-5"
    data-product-variant-picker
    data-config='@json($pickerConfig)'
>
    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Size</p>
        <div class="mt-3 flex flex-wrap gap-2">
            @forelse ($allSizes as $size)
                @php
                    $sizeInStock = $variants->contains(
                        fn (array $variant) => strcasecmp(trim((string) $variant['size']), trim($size)) === 0
                            && $variant['quantity'] > 0,
                    );
                @endphp
                <button
                    type="button"
                    data-variant-size="{{ $size }}"
                    class="variant-size-option variant-size-option--{{ $sizeInStock ? 'available' : 'unavailable' }} min-w-[3rem] border px-3 py-2 text-sm transition"
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
                data-variant-color
                class="input-field mt-0 w-full max-w-[9rem]"
            >
                <option value="">Select color</option>
            </select>
        </div>
    </div>

    <div data-variant-heel-section hidden>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">
            Heel length <span class="normal-case text-brand-muted">(optional)</span>
        </p>
        <div class="mt-3 flex flex-wrap gap-2" data-variant-heel-buttons></div>
    </div>

    <input type="hidden" name="variant_size" value="" data-variant-size-input>
    <input type="hidden" name="variant_color" value="" data-variant-color-input>
    <input type="hidden" name="variant_heel" value="" data-variant-heel-input>

    <p class="text-sm text-brand-muted" data-variant-message></p>

    <div class="flex flex-col gap-4 pt-2">
        <div class="flex items-center gap-3">
            <span class="text-sm uppercase tracking-wide text-brand-muted">Qty</span>
            <div class="flex items-stretch">
                <button
                    type="button"
                    data-variant-quantity-decrease
                    class="flex h-10 w-10 items-center justify-center border border-neutral-300 bg-white text-lg leading-none transition hover:border-brand-red disabled:cursor-not-allowed disabled:opacity-50"
                    aria-label="Decrease quantity"
                    disabled
                >−</button>
                <input
                    id="quantity"
                    type="number"
                    name="quantity"
                    min="1"
                    max="1"
                    value="1"
                    data-variant-quantity
                    class="input-field mt-0 h-10 w-14 rounded-none border-x-0 text-center [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    disabled
                    readonly
                >
                <button
                    type="button"
                    data-variant-quantity-increase
                    class="flex h-10 w-10 items-center justify-center border border-neutral-300 bg-white text-lg leading-none transition hover:border-brand-red disabled:cursor-not-allowed disabled:opacity-50"
                    aria-label="Increase quantity"
                    disabled
                >+</button>
            </div>
        </div>

        <button
            type="submit"
            data-variant-submit
            data-out-of-stock="{{ $product->isInStock() ? 'false' : 'true' }}"
            class="btn-primary w-full py-3 disabled:cursor-not-allowed disabled:opacity-50"
            @disabled(! $product->isInStock())
        >
            {{ $product->isInStock() ? 'Add To Cart' : 'Out of Stock' }}
        </button>
    </div>
</div>
