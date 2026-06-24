@props([
    'product',
    'compact' => false,
    'label' => false,
])

@php
    $shareUrl = route('shop.show', $product);
    $shareTitle = $product->name.' - SACYSHOES';
    $shareText = 'Check out '.$product->name.' on SACYSHOES';
@endphp

<div
    x-data="productShare({
        url: @js($shareUrl),
        title: @js($shareTitle),
        text: @js($shareText),
    })"
    class="inline"
    @if ($compact) @click.stop @endif
>
    <button
        type="button"
        class="product-action-btn {{ $label ? 'product-action-btn-labeled' : '' }}"
        title="Share this product"
        aria-label="Share this product"
        @click="share()"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.935-2.186 2.25 2.25 0 00-3.935 2.186z" />
        </svg>
        @if ($label)
            <span x-text="copied ? 'Link copied' : 'Share'"></span>
        @endif
    </button>
</div>
