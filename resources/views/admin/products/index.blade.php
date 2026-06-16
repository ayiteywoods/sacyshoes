@extends('layouts.admin')

@section('heading', 'Products')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.products.create') }}" class="btn-primary w-full text-center sm:w-auto">Add product</a>
    </div>

    <x-admin-table-panel :page-ids="$products->pluck('id')">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="name" label="Product" class="admin-cell-primary" />
                    <x-admin-sort-th column="sku" label="SKU" class="admin-col-md" />
                    <x-admin-sort-th column="price" label="Price" />
                    <x-admin-sort-th column="quantity" label="Stock" />
                    <x-admin-sort-th column="status" label="Status" class="admin-col-md" />
                    <th class="admin-table-cell admin-col-actions text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <x-admin-table-leading-cells :id="$product->id" :number="$products->firstItem() + $loop->index" />
                        <td class="admin-table-cell admin-cell-primary">
                            <div class="font-medium">{{ $product->name }}</div>
                            <div class="text-brand-muted">{{ $product->category->name }}</div>
                            <div class="mt-1 text-xs text-brand-muted md:hidden">{{ $product->status->label() }}</div>
                        </td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $product->sku }}</td>
                        <td class="admin-table-cell whitespace-nowrap">GHS {{ number_format($product->sellingPrice(), 2) }}</td>
                        <td class="admin-table-cell whitespace-nowrap">
                            {{ $product->quantity }}
                            @if ($product->isLowStock())
                                <span class="text-amber-600">Low</span>
                            @endif
                        </td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $product->status->label() }}</td>
                        <td class="admin-table-cell admin-col-actions">
                            <x-admin-table-actions
                                :view-detail-url="route('admin.details.products', $product)"
                                :edit-url="route('admin.products.edit', $product)"
                                :delete-url="route('admin.products.destroy', $product)"
                                delete-confirm="Delete this product permanently?"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="admin-table-cell py-8 text-center text-brand-muted">No products yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$products" />
@endsection
