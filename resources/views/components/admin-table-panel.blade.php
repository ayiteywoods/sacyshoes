@props(['pageIds' => []])

<div
    x-data="adminTableSelection(@js(collect($pageIds)->map(fn ($id) => (string) $id)->values()))"
    {{ $attributes->merge(['class' => 'card']) }}
>
    <div
        x-show="selected.length > 0"
        x-cloak
        class="flex items-center justify-between border-b border-neutral-200 bg-brand-light px-4 py-3 text-sm sm:px-6"
    >
        <span><span x-text="selected.length"></span> selected</span>
        <button type="button" class="font-medium text-brand-red hover:underline" @click="clearSelection()">
            Clear selection
        </button>
    </div>

    <div class="admin-table-scroll">
        {{ $slot }}
    </div>
</div>
