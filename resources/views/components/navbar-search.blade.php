@props([
    'value' => request('q'),
])

<form
    action="{{ route('shop.index') }}"
    method="GET"
    {{ $attributes->merge(['class' => 'flex w-full gap-2']) }}
>
    <label for="navbar-search" class="sr-only">Search products</label>
    <input
        id="navbar-search"
        type="search"
        name="q"
        value="{{ $value }}"
        placeholder="Search shoes by name or SKU..."
        class="input-field mt-0 min-w-0 flex-1"
    >
    <button type="submit" class="btn-primary shrink-0 px-5">Search</button>
</form>
