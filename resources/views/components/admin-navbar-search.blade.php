@props([
    'value' => request('q'),
    'inputId' => 'admin-navbar-search',
])

<form
    action="{{ route('admin.search') }}"
    method="GET"
    {{ $attributes->merge(['class' => 'admin-navbar-search']) }}
    role="search"
>
    <label for="{{ $inputId }}" class="sr-only">Search admin</label>
    <div class="relative">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
        <input
            id="{{ $inputId }}"
            type="search"
            name="q"
            value="{{ $value }}"
            placeholder="Search products, orders, customers..."
            class="admin-navbar-search-input"
            autocomplete="off"
        >
    </div>
</form>
