@extends('layouts.admin')

@section('heading', 'Categories')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.categories.create') }}" class="btn-primary w-full text-center sm:w-auto">Add category</a>
    </div>

    <x-admin-table-panel :page-ids="$categories->pluck('id')">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="name" label="Name" class="admin-cell-primary" />
                    <x-admin-sort-th column="products_count" label="Products" />
                    <x-admin-sort-th column="status" label="Status" class="admin-col-md" />
                    <th class="admin-table-cell admin-col-actions text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <x-admin-table-leading-cells :id="$category->id" :number="$categories->firstItem() + $loop->index" />
                        <td class="admin-table-cell admin-cell-primary font-medium">{{ $category->name }}</td>
                        <td class="admin-table-cell whitespace-nowrap">{{ $category->products_count }}</td>
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
                        <td colspan="6" class="admin-table-cell py-8 text-center text-brand-muted">No categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$categories" />
@endsection
