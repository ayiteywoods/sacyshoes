@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'admin-pagination-wrap']) }}>
        {{ $paginator->links() }}
    </div>
@endif
