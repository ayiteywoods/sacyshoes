@props([
    'href' => route('home'),
    'size' => 'header',
    'variant' => 'light',
])

@php
    $imageClass = match ($size) {
        'header' => 'h-8 w-auto object-contain',
        'admin' => 'h-8 w-auto object-contain',
        'auth' => 'h-12 w-auto object-contain',
        default => 'h-8 w-auto object-contain',
    };

    $textClass = match ($size) {
        'header' => 'text-sm font-semibold tracking-[0.15em]',
        'admin' => 'text-sm font-semibold tracking-[0.15em]',
        'auth' => 'text-base font-semibold tracking-[0.2em] sm:text-lg',
        default => 'text-sm font-semibold tracking-[0.15em]',
    };

    $shoesColor = $variant === 'dark' ? 'text-white' : 'text-brand-black';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-2.5']) }}>
    <img
        src="{{ asset('images/brand/logo1.webp') }}"
        alt="{{ config('app.name', 'Sacy Shoes') }}"
        class="{{ $imageClass }}"
    >
    <span class="{{ $textClass }}">
        <span class="text-brand-red">SACY</span><span class="{{ $shoesColor }}">SHOES</span>
    </span>
</a>
