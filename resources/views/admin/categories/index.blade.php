@extends('layouts.admin')

@section('heading', 'Categories')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.categories.shop') }}" class="btn-outline w-full text-center sm:w-auto">Shop category order</a>
        <a href="{{ route('admin.categories.navbar') }}" class="btn-outline w-full text-center sm:w-auto">Navbar categories</a>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary w-full text-center sm:w-auto">Add category</a>
    </div>

    <x-admin-table-panel :page-ids="$categories->pluck('id')">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="name" label="Name" class="admin-cell-primary" />
                    <th class="admin-table-cell font-medium">Type</th>
                    <x-admin-sort-th column="products_count" label="Products" />
                    <th class="admin-table-cell font-medium">Navbar</th>
                    <x-admin-sort-th column="status" label="Status" class="admin-col-md" />
                    <th class="admin-table-cell admin-col-actions text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <x-admin-table-leading-cells :id="$category->id" :number="$categories->firstItem() + $loop->index" />
                        <td class="admin-table-cell admin-cell-primary font-medium">
                            @if ($category->parent)
                                <span class="text-brand-muted">↳</span> {{ $category->name }}
                                <span class="mt-0.5 block text-xs font-normal text-brand-muted">under {{ $category->parent->name }}</span>
                            @else
                                {{ $category->name }}
                            @endif
                        </td>
                        <td class="admin-table-cell whitespace-nowrap">
                            @if ($category->parent)
                                <span class="text-brand-muted">Subcategory</span>
                            @else
                                Main
                                @if ($category->children_count > 0)
                                    <span class="text-brand-muted">({{ $category->children_count }} sub)</span>
                                @endif
                            @endif
                        </td>
                        <td class="admin-table-cell whitespace-nowrap">{{ $category->products_count }}</td>
                        <td class="admin-table-cell whitespace-nowrap">
                            @if ($category->show_in_navbar)
                                <span class="text-green-700">Yes</span>
                                <span class="text-brand-muted">({{ $category->navbar_sort_order }})</span>
                            @else
                                <span class="text-brand-muted">No</span>
                            @endif
                        </td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $category->status->label() }}</td>
                        <td class="admin-table-cell admin-col-actions">
                            <x-admin-table-actions
                                :view-detail-url="route('admin.details.categories', $category)"
                                :edit-url="route('admin.categories.edit', $category)"
                                :delete-url="route('admin.categories.destroy', $category)"
                                :delete-confirm="$category->products_count > 0
                                    ? 'This category has '.$category->products_count.' product(s). Deleting it will remove them too. Continue?'
                                    : 'Delete this category permanently?'"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="admin-table-cell py-8 text-center text-brand-muted">No categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$categories" />
@endsection
