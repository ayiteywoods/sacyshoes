@php
    $heading = 'Shop All';
    $eyebrow = 'Our Collection';
    $description = 'Discover premium footwear curated for every occasion — from everyday comfort to statement style.';

    if (request()->filled('q')) {
        $heading = 'Results for “'.request('q').'”';
        $eyebrow = 'Search';
        $description = 'Browse matching styles across our full footwear collection.';
    } elseif ($activeCategory) {
        $heading = $activeCategory->name;
        $eyebrow = $activeCategory->parent ? $activeCategory->parent->name : 'Category';
        $description = $activeCategory->description ?: $description;
    }

    $stats = [
        ['value' => (string) $products->total(), 'label' => 'Products', 'icon' => 'bag', 'tone' => 'red'],
        ['value' => (string) $categoryTree->count(), 'label' => 'Categories', 'icon' => 'tag', 'tone' => 'white'],
        ['value' => 'Ghana', 'label' => 'Delivery', 'icon' => 'truck', 'tone' => 'red'],
    ];
@endphp

<x-page-hero
    :eyebrow="$eyebrow"
    :title="$heading"
    :description="$description"
    icon="cart"
    :stats="$stats"
>
    @if ($activeCategory || request()->filled('q') || request()->boolean('in_stock'))
        <x-slot:chips>
            @if ($activeCategory)
                <a href="{{ route('shop.index', request()->except('category')) }}" class="shop-hero-chip">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        @include('components.partials.hero-icon', ['name' => 'tag'])
                    </svg>
                    {{ $activeCategory->name }}
                    <span class="text-white/50">&times;</span>
                </a>
            @endif

            @if (request()->filled('q'))
                <a href="{{ route('shop.index', request()->except('q')) }}" class="shop-hero-chip">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    “{{ request('q') }}”
                    <span class="text-white/50">&times;</span>
                </a>
            @endif

            @if (request()->boolean('in_stock'))
                <a href="{{ route('shop.index', request()->except('in_stock')) }}" class="shop-hero-chip">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    In stock only
                    <span class="text-white/50">&times;</span>
                </a>
            @endif
        </x-slot:chips>
    @endif
</x-page-hero>
