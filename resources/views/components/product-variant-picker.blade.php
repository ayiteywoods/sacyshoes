@props(['product'])

@php
    $pickerId = 'product-variant-picker-'.$product->id;

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

    $allColors = [];
    $seenColors = [];

    foreach ($variants as $variant) {
        $color = (string) ($variant['color'] ?? '');
        $key = strtolower(trim($color));

        if ($key === '' || isset($seenColors[$key])) {
            continue;
        }

        $seenColors[$key] = true;
        $allColors[] = $color;
    }

    usort($allColors, 'strnatcasecmp');

    $pickerConfig = [
        'variants' => $variants->values()->all(),
        'initialSize' => old('variant_size'),
        'initialColor' => old('variant_color'),
        'initialHeel' => old('variant_heel'),
    ];
@endphp

<style>
    #{{ $pickerId }} .variant-size-option--available {
        display: inline-flex;
        min-width: 3rem;
        align-items: center;
        justify-content: center;
        border: 1px solid #a3a3a3;
        background-color: #fff;
        color: #111;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        cursor: pointer;
        transition: border-color 150ms, background-color 150ms, color 150ms;
    }

    #{{ $pickerId }} .variant-size-option--unavailable {
        display: inline-flex;
        min-width: 3rem;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border: 1px solid #171717;
        background-color: #fff;
        color: #111;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        cursor: not-allowed;
    }

    #{{ $pickerId }} .variant-size-option--unavailable::after {
        content: '';
        position: absolute;
        top: 50%;
        left: -15%;
        width: 130%;
        height: 1px;
        background-color: currentColor;
        transform: rotate(-35deg);
        pointer-events: none;
    }

    #{{ $pickerId }} .variant-size-radio:checked + .variant-size-option--available,
    #{{ $pickerId }} .variant-size-option--available.is-selected {
        border-color: #e10600 !important;
        background-color: #e10600 !important;
        color: #fff !important;
    }

    #{{ $pickerId }} .variant-size-option--available:hover {
        border-color: #e10600;
    }

    #{{ $pickerId }} .variant-size-radio:checked + .variant-size-option--available:hover,
    #{{ $pickerId }} .variant-size-option--available.is-selected:hover {
        background-color: #e10600 !important;
        color: #fff !important;
    }
</style>

<div
    id="{{ $pickerId }}"
    class="product-variant-picker space-y-5"
    data-product-variant-picker
>
    <div>
        <div class="flex items-center gap-4">
            <label for="variant-color-{{ $product->id }}" class="shrink-0 text-sm lowercase text-brand-muted">color</label>
            <select
                id="variant-color-{{ $product->id }}"
                data-variant-color
                class="input-field mt-0 w-full max-w-[9rem]"
            >
                <option value="">Select color</option>
                @foreach ($allColors as $color)
                    <option value="{{ $color }}" @selected(old('variant_color') === $color)>{{ $color }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div data-variant-size-section>
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Size</p>
        <div class="mt-3 flex flex-wrap gap-2" data-variant-size-options>
            <p class="text-sm text-brand-muted">Select a color to see available sizes.</p>
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
                    id="quantity-{{ $product->id }}"
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

@include('components.partials.variant-picker-inline-script', [
    'pickerId' => $pickerId,
    'pickerConfig' => $pickerConfig,
])
