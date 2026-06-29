@php
    $itemCount = $items->count();
    $description = $itemCount > 0
        ? "Review your selected styles, update quantities, and proceed when you're ready."
        : 'Your bag is empty. Browse our collection and add something you love.';

    $stats = $itemCount > 0
        ? [
            ['value' => (string) $itemCount, 'label' => Str::plural('Item', $itemCount), 'icon' => 'bag', 'tone' => 'red'],
            ['value' => config('shop.currency_symbol').' '.number_format($subtotal, 0), 'label' => 'Subtotal', 'icon' => 'cart', 'tone' => 'white'],
            ['value' => 'Pay', 'label' => 'To confirm', 'icon' => 'calendar', 'tone' => 'red'],
        ]
        : [
            ['value' => '0', 'label' => 'Items', 'icon' => 'bag', 'tone' => 'red'],
            ['value' => config('shop.currency_symbol').' 0', 'label' => 'Subtotal', 'icon' => 'cart', 'tone' => 'white'],
            ['value' => 'Shop', 'label' => 'Browse', 'icon' => 'tag', 'tone' => 'red'],
        ];
@endphp

<x-page-hero
    eyebrow="Your Bag"
    title="Shopping Cart"
    :description="$description"
    icon="cart"
    :stats="$stats"
>
    <x-slot:actions>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 border border-white/20 bg-white/5 px-5 py-2.5 text-sm font-medium text-white transition hover:border-white/40 hover:bg-white/10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Continue Shopping
        </a>
    </x-slot:actions>
</x-page-hero>
