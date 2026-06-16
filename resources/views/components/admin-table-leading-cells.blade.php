@props(['id', 'number'])

<td class="admin-table-cell w-10">
    <input
        type="checkbox"
        class="h-4 w-4 rounded border-neutral-300 text-brand-red focus:ring-brand-red/20"
        value="{{ e((string) $id) }}"
        x-model="selected"
        aria-label="Select row"
    >
</td>
<td class="admin-table-cell admin-col-num w-10 text-center text-brand-muted">{{ $number }}</td>
