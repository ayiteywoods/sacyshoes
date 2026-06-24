@php($region = $region ?? null)

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form-label :required="true">Region name</x-form-label>
        <input type="text" name="name" value="{{ old('name', $region?->name) }}" required class="input-field">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Sort order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $region?->sort_order ?? 0) }}" class="input-field">
        @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center gap-3 pt-7">
        <label class="inline-flex items-center gap-2 text-sm text-brand-muted">
            <input type="hidden" name="is_accra" value="0">
            <input type="checkbox" name="is_accra" value="1" class="rounded-none border-neutral-300 text-brand-red focus:ring-brand-red" @checked(old('is_accra', $region?->is_accra))>
            This is Accra (pay rider on delivery)
        </label>
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-brand-muted">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded-none border-neutral-300 text-brand-red focus:ring-brand-red" @checked(old('is_active', $region?->is_active ?? true))>
            Active
        </label>
    </div>
</div>

<div class="mt-8 border-t border-neutral-200 pt-6" x-data="shippingOptionsForm(@js(old('options', $region?->options?->map(fn ($option) => [
    'id' => $option->id,
    'name' => $option->name,
    'price' => $option->price,
    'description' => $option->description,
    'sort_order' => $option->sort_order,
    'is_active' => $option->is_active,
])->values()->all() ?? [])))">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide">Delivery options</h3>
            <p class="mt-1 text-xs text-brand-muted">Shown at checkout when this region is selected (not needed for Accra).</p>
        </div>
        <button type="button" class="btn-outline px-3 py-2 text-xs" @click="addRow()">Add option</button>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-neutral-200 text-left text-xs uppercase tracking-wide text-brand-muted">
                <tr>
                    <th class="px-2 py-2">Name <span class="text-brand-red">*</span></th>
                    <th class="px-2 py-2">Price <span class="text-brand-red">*</span></th>
                    <th class="px-2 py-2">Description</th>
                    <th class="px-2 py-2">Sort</th>
                    <th class="px-2 py-2">Active</th>
                    <th class="px-2 py-2"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in rows" :key="index">
                    <tr class="border-b border-neutral-100 align-top">
                        <td class="px-2 py-2">
                            <input type="hidden" :name="`options[${index}][id]`" :value="row.id || ''">
                            <input type="text" :name="`options[${index}][name]`" x-model="row.name" required class="input-field min-w-[12rem]">
                        </td>
                        <td class="px-2 py-2">
                            <input type="number" step="0.01" min="0" :name="`options[${index}][price]`" x-model="row.price" required class="input-field w-32">
                        </td>
                        <td class="px-2 py-2">
                            <textarea rows="2" :name="`options[${index}][description]`" x-model="row.description" class="input-field min-w-[16rem]"></textarea>
                        </td>
                        <td class="px-2 py-2">
                            <input type="number" min="0" :name="`options[${index}][sort_order]`" x-model="row.sort_order" class="input-field w-24">
                        </td>
                        <td class="px-2 py-2 text-center">
                            <input type="hidden" :name="`options[${index}][is_active]`" :value="row.is_active ? 1 : 0">
                            <input type="checkbox" x-model="row.is_active" class="rounded-none border-neutral-300 text-brand-red focus:ring-brand-red">
                        </td>
                        <td class="px-2 py-2">
                            <button type="button" class="text-xs text-brand-red hover:underline" @click="removeRow(index)">Remove</button>
                        </td>
                    </tr>
                </template>

                <tr x-show="rows.length === 0">
                    <td colspan="6" class="px-2 py-6 text-center text-sm text-brand-muted">No options added yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

    @error('options')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    @error('options.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('shippingOptionsForm', (initialRows = []) => ({
                    rows: initialRows ?? [],
                    addRow() {
                        this.rows.push({
                            name: '',
                            price: 0,
                            description: '',
                            sort_order: 0,
                            is_active: true,
                        });
                    },
                    removeRow(index) {
                        this.rows.splice(index, 1);
                    },
                }));
            });
        </script>
    @endpush
@endonce

