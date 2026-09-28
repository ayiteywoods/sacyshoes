@props([
    'rows',
    'empty' => 'No paid sales or stock for this period.',
])

@php
    $max = max((int) collect($rows)->max('units_sold'), 1);
@endphp

<div class="space-y-3 px-4 py-4 sm:px-6">
    @forelse ($rows as $row)
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="w-24 shrink-0 truncate font-medium sm:w-28">{{ $row->label }}</span>
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <div class="h-2 flex-1 bg-neutral-100">
                    <div class="h-2 bg-brand-red" style="width: {{ round(($row->units_sold / $max) * 100) }}%"></div>
                </div>
                <div class="w-28 shrink-0 text-right leading-tight">
                    <p class="font-medium">{{ number_format($row->units_sold) }} sold</p>
                    <p class="text-xs {{ ($row->stock_left ?? 0) < 10 ? 'font-medium text-brand-red' : 'text-brand-muted' }}">
                        {{ number_format($row->stock_left ?? 0) }} in stock
                    </p>
                </div>
            </div>
        </div>
    @empty
        <p class="py-4 text-sm text-brand-muted">{{ $empty }}</p>
    @endforelse
</div>
