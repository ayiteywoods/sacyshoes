@php
    $inStock = $product->isInStock();
@endphp

<div class="card group relative overflow-hidden transition hover:shadow-lg">
    <a href="{{ route('shop.show', $product) }}" class="block">
        @if (! $inStock)
            <span class="sold-out-badge">Sold Out</span>
        @elseif ($product->discount_price)
            <span class="sale-badge">Sale</span>
        @endif

        <div class="product-card-image aspect-square overflow-hidden bg-neutral-100">
            @if ($product->primaryImage())
                <img
                    src="{{ asset('storage/'.$product->primaryImage()->path) }}"
                    alt="{{ $product->name }}"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                >
            @else
                <div class="flex h-full items-center justify-center text-sm text-brand-muted">No image</div>
            @endif
        </div>

        <div class="p-4 pb-2">
            <h3 class="truncate font-medium uppercase tracking-wide text-brand-black">{{ $product->name }}</h3>
        </div>
    </a>

    <div class="flex items-center justify-between gap-2 px-4 pb-4">
        <div class="min-w-0">
            <a href="{{ route('shop.show', $product) }}" class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-brand-red">
                    {{ config('shop.currency_symbol') }} {{ number_format($product->sellingPrice(), 2) }}
                </span>
                @if ($product->discount_price)
                    <span class="text-sm text-brand-muted line-through">{{ config('shop.currency_symbol') }} {{ number_format($product->price, 2) }}</span>
                @endif
            </a>
        </div>

        <div class="flex shrink-0 items-center gap-1.5">
            <x-product-favorite-button :product="$product" compact />
            {{-- Share disabled for now
            <x-product-share-button :product="$product" compact />
            --}}
        </div>
    </div>
</div>
