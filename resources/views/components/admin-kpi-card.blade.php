@props([
    'label',
    'value',
    'change' => null,
    'format' => 'number',
    'highlight' => false,
])

@php
    $displayValue = match ($format) {
        'currency' => 'GHS '.number_format((float) $value, 2),
        default => is_numeric($value) ? number_format((float) $value) : $value,
    };
@endphp

<div {{ $attributes->merge(['class' => 'admin-kpi-card card p-4 sm:p-5'.($highlight ? ' border-red-200 bg-red-50' : '')]) }}>
    <p class="text-xs uppercase tracking-wide {{ $highlight ? 'text-brand-red' : 'text-brand-muted' }}">{{ $label }}</p>
    <p class="mt-2 text-2xl font-semibold {{ $highlight ? 'text-brand-black' : ($format === 'currency' ? 'text-brand-red' : 'text-brand-black') }}">
        {{ $displayValue }}
    </p>

    @if ($change !== null)
        <p class="mt-2 flex items-center gap-1 text-xs font-medium {{ $change >= 0 ? 'text-green-700' : 'text-red-700' }}">
            @if ($change >= 0)
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/>
                </svg>
            @else
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            @endif
            <span>{{ $change > 0 ? '+' : '' }}{{ $change }}% vs previous period</span>
        </p>
    @endif
</div>
