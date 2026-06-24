@props([
    'value',
    'label',
    'icon' => 'info',
    'tone' => 'red',
])

<div class="shop-hero-stat min-w-0">
    <span class="shop-hero-stat-icon {{ $tone === 'white' ? 'shop-hero-stat-icon-white' : 'shop-hero-stat-icon-red' }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            @include('components.partials.hero-icon', ['name' => $icon])
        </svg>
    </span>
    <div class="min-w-0 flex-1">
        <p class="break-all text-sm font-semibold leading-snug sm:text-base" title="{{ $value }}">{{ $value }}</p>
        <p class="mt-1 text-[10px] uppercase tracking-wider text-neutral-400">{{ $label }}</p>
    </div>
</div>
