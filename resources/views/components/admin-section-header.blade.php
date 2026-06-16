@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'linkLabel' => 'View all',
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-3 border-b border-neutral-200 px-4 py-4 sm:px-6']) }}>
    <div>
        <h2 class="font-semibold">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-1 text-sm text-brand-muted">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($href)
        <a href="{{ $href }}" class="text-sm font-medium text-brand-red hover:underline">{{ $linkLabel }}</a>
    @endif

    {{ $slot }}
</div>
