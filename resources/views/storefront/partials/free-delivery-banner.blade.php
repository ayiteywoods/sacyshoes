@php
    $section = $section ?? null;
    $text = $section?->body ?? 'Free delivery on orders over {currency_symbol} {threshold} — Delivered across Ghana';
    $text = str_replace(
        ['{currency_symbol}', '{threshold}'],
        [config('shop.currency_symbol'), number_format(config('shop.free_delivery_threshold'), 0)],
        $text,
    );
@endphp

@if (! $section || $section->is_active)
    <div class="border-y border-neutral-800 bg-brand-black py-2.5 text-center text-xs text-white sm:text-sm">
        <p>{{ $text }}</p>
    </div>
@endif
