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

        if ($key === '') {
            continue;
        }

        if (! isset($seenColors[$key])) {
            $seenColors[$key] = [
                'value' => $color,
                'in_stock' => false,
            ];
        }

        if ((int) ($variant['quantity'] ?? 0) > 0) {
            $seenColors[$key]['in_stock'] = true;
        }
    }

    $allColors = array_values($seenColors);
    usort($allColors, fn ($left, $right) => strnatcasecmp($left['value'], $right['value']));

    $pickerConfig = [
        'variants' => $variants->values()->all(),
        'requiresSize' => $product->requiresSizeSelection(),
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

    #{{ $pickerId }} .variant-color-dropdown {
        position: relative;
        width: 100%;
        max-width: 9rem;
    }

    #{{ $pickerId }} .variant-color-button {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        text-align: left;
        cursor: pointer;
    }

    #{{ $pickerId }} .variant-color-button[aria-expanded="true"] .variant-color-chevron {
        transform: rotate(180deg);
    }

    #{{ $pickerId }} .variant-color-chevron {
        flex-shrink: 0;
        width: 1rem;
        height: 1rem;
        opacity: 0.55;
        transition: transform 150ms;
    }

    #{{ $pickerId }} .variant-color-menu {
        position: absolute;
        left: 0;
        z-index: 40;
        margin-top: 0.25rem;
        min-width: 14rem;
        max-height: 16rem;
        overflow-y: auto;
        border: 1px solid #e5e5e5;
        background: #fff;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        padding: 0.25rem 0;
    }

    #{{ $pickerId }} .variant-color-option {
        display: flex;
        width: 100%;
        align-items: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #111;
        background: transparent;
        text-align: left;
        cursor: pointer;
    }

    #{{ $pickerId }} .variant-color-option:hover,
    #{{ $pickerId }} .variant-color-option:focus-visible {
        background: #f5f5f5;
        outline: none;
    }

    #{{ $pickerId }} .variant-color-option.is-selected {
        background: #e10600;
        color: #fff;
    }

    #{{ $pickerId }} .variant-color-option.is-selected:hover,
    #{{ $pickerId }} .variant-color-option.is-selected:focus-visible {
        background: #e10600;
        color: #fff;
    }

    #{{ $pickerId }} .variant-color-option--unavailable {
        color: #737373;
        text-decoration: line-through;
        cursor: not-allowed;
    }

    #{{ $pickerId }} .variant-color-option--unavailable:hover,
    #{{ $pickerId }} .variant-color-option--unavailable:focus-visible {
        background: transparent;
        color: #737373;
    }
</style>

<div
    id="{{ $pickerId }}"
    class="product-variant-picker space-y-5"
    data-product-variant-picker
>
    <div>
        <div class="flex items-center gap-4">
            <span id="variant-color-label-{{ $product->id }}" class="shrink-0 text-sm lowercase text-brand-muted">color</span>
            <div class="variant-color-dropdown" data-variant-color-dropdown>
                <button
                    type="button"
                    id="variant-color-{{ $product->id }}"
                    class="input-field variant-color-button mt-0"
                    data-variant-color-button
                    aria-haspopup="listbox"
                    aria-expanded="false"
                    aria-labelledby="variant-color-label-{{ $product->id }} variant-color-{{ $product->id }}"
                >
                    <span data-variant-color-label>{{ old('variant_color') ?: 'Select color' }}</span>
                    <svg class="variant-color-chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
                <ul class="variant-color-menu" data-variant-color-menu role="listbox" hidden>
                    <li>
                        <button
                            type="button"
                            class="variant-color-option"
                            data-variant-color-option=""
                            role="option"
                            aria-selected="{{ old('variant_color') ? 'false' : 'true' }}"
                        >Select color</button>
                    </li>
                    @foreach ($allColors as $color)
                        <li>
                            <button
                                type="button"
                                class="variant-color-option @if (! $color['in_stock']) variant-color-option--unavailable @endif"
                                data-variant-color-option="{{ $color['value'] }}"
                                data-in-stock="{{ $color['in_stock'] ? 'true' : 'false' }}"
                                role="option"
                                aria-selected="{{ old('variant_color') === $color['value'] ? 'true' : 'false' }}"
                                @disabled(! $color['in_stock'])
                            >{{ $color['value'] }}</button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div data-variant-size-section @if (! $product->requiresSizeSelection()) hidden @endif>
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
