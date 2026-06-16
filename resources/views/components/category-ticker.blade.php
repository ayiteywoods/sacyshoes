@php
    $staticItems = [
        ['label' => 'New Arrivals', 'url' => route('shop.index'), 'icon' => 'sparkle'],
        ['label' => 'All Products', 'url' => route('shop.index'), 'icon' => 'bag'],
        ['label' => 'Sale Items', 'url' => route('shop.index'), 'icon' => 'tag'],
    ];

    $categoryIcons = [
        'sneakers' => 'shoe',
        'formal' => 'heel',
        'sandals' => 'shoe',
        'boots' => 'shoe',
        'heels' => 'heel',
        'flats' => 'shoe',
        'bags' => 'bag',
    ];

    $tickerItems = collect($staticItems);

    foreach ($floatingCategories ?? [] as $category) {
        $tickerItems->push([
            'label' => $category->name,
            'url' => route('shop.index', ['category' => $category->id]),
            'icon' => $categoryIcons[strtolower($category->slug)] ?? $categoryIcons[strtolower($category->name)] ?? 'shoe',
        ]);
    }

    $tickerItems->push(['label' => 'Fast Delivery', 'url' => null, 'icon' => 'truck']);

    $tickerItems = $tickerItems->merge($tickerItems)->merge($tickerItems);
@endphp

<div class="ticker-bar" aria-label="Browse collections">
    <div class="ticker-track">
        @foreach ([1, 2] as $copy)
            <div class="ticker-content" @if($copy === 2) aria-hidden="true" @endif>
                @foreach ($tickerItems as $item)
                    @if ($item['url'])
                        <a href="{{ $item['url'] }}" class="ticker-item">
                            <x-nav-icon :icon="$item['icon']" class="h-2.5 w-2.5 shrink-0 text-white/90" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @else
                        <span class="ticker-item">
                            <x-nav-icon :icon="$item['icon']" class="h-2.5 w-2.5 shrink-0 text-white/90" />
                            <span>{{ $item['label'] }}</span>
                        </span>
                    @endif
                    <span class="ticker-separator" aria-hidden="true">&bull;</span>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
