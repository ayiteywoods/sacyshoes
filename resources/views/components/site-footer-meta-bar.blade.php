@props([
    'variant' => 'dark',
    'embedded' => false,
])

@php
    $isDark = $variant === 'dark';
@endphp

@if ($embedded)
    <div {{ $attributes->merge(['class' => 'flex flex-col gap-2 text-xs sm:flex-row sm:items-center sm:justify-between']) }}>
        @include('components.partials.site-footer-meta-bar-content', ['isDark' => $isDark])
    </div>
@else
    <div
        {{ $attributes->merge(['class' => 'border-t '.($isDark ? 'border-neutral-800 bg-brand-black text-neutral-500' : 'border-neutral-200 bg-brand-white text-brand-muted')]) }}
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-4 text-xs sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            @include('components.partials.site-footer-meta-bar-content', ['isDark' => $isDark])
        </div>
    </div>
@endif
