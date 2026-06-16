<th class="admin-table-cell w-10">
    <input
        type="checkbox"
        class="h-4 w-4 rounded border-neutral-300 text-brand-red focus:ring-brand-red/20"
        :checked="allSelected"
        :indeterminate="someSelected"
        @change="toggleAll()"
        aria-label="Select all rows on this page"
    >
</th>
<th class="admin-table-cell admin-col-num w-10 text-center text-xs font-medium uppercase tracking-wide text-brand-muted">#</th>
