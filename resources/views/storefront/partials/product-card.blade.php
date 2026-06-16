<a href="{{ route('shop.show', $product) }}" class="card group relative overflow-hidden transition hover:shadow-lg">
    @if ($product->discount_price)
        <span class="sale-badge">Sale</span>
    @endif

    <div class="aspect-square overflow-hidden bg-neutral-100">
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

    <div class="p-4">
        <h3 class="truncate font-medium uppercase tracking-wide text-brand-black">{{ $product->name }}</h3>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="font-semibold text-brand-red">{{ config('shop.currency_symbol') }} {{ number_format($product->sellingPrice(), 2) }}</span>
            @if ($product->discount_price)
                <span class="text-sm text-brand-muted line-through">{{ config('shop.currency_symbol') }} {{ number_format($product->price, 2) }}</span>
            @endif
        </div>
    </div>
</a>
