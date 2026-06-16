@props([
    'column',
    'label',
    'align' => 'left',
    'sortKey' => 'sort',
    'directionKey' => 'direction',
    'pageKey' => 'page',
])

@php
    $isSorted = request($sortKey) === $column;
    $direction = request($directionKey, 'asc');
    $nextDirection = ($isSorted && $direction === 'asc') ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(array_merge(request()->query(), [
        $sortKey => $column,
        $directionKey => $nextDirection,
        $pageKey => null,
    ]));
@endphp

<th {{ $attributes->merge(['class' => "admin-table-cell text-{$align} font-medium whitespace-nowrap"]) }}>
    <a href="{{ $url }}" class="inline-flex items-center gap-1.5 transition hover:text-brand-red">
        <span>{{ $label }}</span>
        @if ($isSorted)
            <span class="text-brand-red" aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
        @else
            <span class="text-brand-muted/40" aria-hidden="true">↕</span>
        @endif
    </a>
</th>
