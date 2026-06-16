@props([
    'symbol' => config('shop.currency_symbol', 'GH₵'),
    'code' => config('shop.currency', 'GHS'),
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-1.5 rounded-none border border-neutral-200 bg-brand-light px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-brand-black']) }}>
    <span class="text-brand-red">{{ $symbol }}</span>
    <span class="hidden text-brand-muted sm:inline">{{ $code }}</span>
</div>
