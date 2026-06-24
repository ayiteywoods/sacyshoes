@extends('layouts.admin')

@section('heading', 'Shop by category')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-brand-muted">
            Control how active categories appear in the homepage “Shop by category” section and the shop filters. Lower sort numbers appear first.
        </p>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('admin.dashboard') }}" class="btn-outline w-full text-center sm:w-auto">Back to dashboard</a>
            <a href="{{ route('admin.categories.index') }}" class="btn-outline w-full text-center sm:w-auto">All categories</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.categories.shop.update') }}" class="card p-6">
        @csrf
        @method('PUT')

        @if ($categories->isEmpty())
            <p class="text-sm text-brand-muted">
                No active categories yet.
                <a href="{{ route('admin.categories.create') }}" class="font-medium text-brand-red hover:underline">Create a category</a>
                first, then return here to arrange how it appears on the shop.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="admin-data-table min-w-full">
                    <thead>
                        <tr>
                            <th class="admin-table-cell text-left font-medium">Category</th>
                            <th class="admin-table-cell text-left font-medium">Sort order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $index = 0; @endphp
                        @foreach ($categories as $category)
                            <tr>
                                <td class="admin-table-cell font-medium">
                                    <input type="hidden" name="categories[{{ $index }}][id]" value="{{ $category->id }}">
                                    {{ $category->name }}
                                    <span class="block text-xs font-normal text-brand-muted">Main category</span>
                                </td>
                                <td class="admin-table-cell">
                                    <input
                                        type="number"
                                        name="categories[{{ $index }}][shop_sort_order]"
                                        value="{{ old("categories.$index.shop_sort_order", $category->shop_sort_order) }}"
                                        min="0"
                                        max="9999"
                                        class="input-field w-24"
                                    >
                                </td>
                            </tr>
                            @php $index++; @endphp

                            @foreach ($category->children as $child)
                                <tr>
                                    <td class="admin-table-cell">
                                        <input type="hidden" name="categories[{{ $index }}][id]" value="{{ $child->id }}">
                                        <span class="text-brand-muted">↳</span> {{ $child->name }}
                                        <span class="block text-xs text-brand-muted">Subcategory of {{ $category->name }}</span>
                                    </td>
                                    <td class="admin-table-cell">
                                        <input
                                            type="number"
                                            name="categories[{{ $index }}][shop_sort_order]"
                                            value="{{ old("categories.$index.shop_sort_order", $child->shop_sort_order) }}"
                                            min="0"
                                            max="9999"
                                            class="input-field w-24"
                                        >
                                    </td>
                                </tr>
                                @php $index++; @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-primary w-full sm:w-auto">Save shop category order</button>
            </div>
        @endif
    </form>
@endsection
