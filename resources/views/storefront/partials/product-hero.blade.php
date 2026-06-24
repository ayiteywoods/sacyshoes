@php
    $categoryLabel = $product->category->parent
        ? $product->category->parent->name.' / '.$product->category->name
        : $product->category->name;

    $description = $product->description
        ? \Illuminate\Support\Str::limit(strip_tags($product->description), 160)
        : 'Premium footwear curated for every occasion.';

    $stats = [
        [
            'value' => config('shop.currency_symbol').' '.number_format($product->sellingPrice(), 0),
            'label' => $product->discount_price ? 'Sale price' : 'Price',
            'icon' => 'cart',
            'tone' => 'red',
        ],
        [
            'value' => $product->sku,
            'label' => 'SKU',
            'icon' => 'tag',
            'tone' => 'white',
        ],
        [
            'value' => $product->isInStock() ? 'Available' : 'Sold out',
            'label' => 'Stock',
            'icon' => 'bag',
            'tone' => 'red',
        ],
    ];
@endphp

<x-page-hero
    :eyebrow="$categoryLabel"
    :title="$product->name"
    :description="$description"
    icon="bag"
    :stats="$stats"
>
    <x-slot:chips>
        <a href="{{ route('home') }}" class="shop-hero-chip">Home</a>
        <a href="{{ route('shop.index') }}" class="shop-hero-chip">Shop</a>
        @if ($product->category->parent)
            <a href="{{ route('shop.index', ['category' => $product->category->parent->id]) }}" class="shop-hero-chip">
                {{ $product->category->parent->name }}
            </a>
        @endif
        <a href="{{ route('shop.index', ['category' => $product->category->id]) }}" class="shop-hero-chip">
            {{ $product->category->name }}
        </a>
    </x-slot:chips>
</x-page-hero>
