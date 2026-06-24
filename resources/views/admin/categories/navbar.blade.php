@extends('layouts.admin')

@section('heading', 'Storefront Navbar')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-brand-muted">
            Choose which active main categories appear in the storefront header and scrolling ticker. Lower sort numbers appear first.
        </p>
        <a href="{{ route('admin.categories.index') }}" class="btn-outline w-full text-center sm:w-auto">Back to categories</a>
    </div>

    <form method="POST" action="{{ route('admin.categories.navbar.update') }}" class="card p-6">
        @csrf
        @method('PUT')

        @if ($categories->isEmpty())
            <p class="text-sm text-brand-muted">
                No active categories yet.
                <a href="{{ route('admin.categories.create') }}" class="font-medium text-brand-red hover:underline">Create a category</a>
                first, then return here to add it to the navbar.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="admin-data-table min-w-full">
                    <thead>
                        <tr>
                            <th class="admin-table-cell text-left font-medium">Show in navbar</th>
                            <th class="admin-table-cell text-left font-medium">Category</th>
                            <th class="admin-table-cell text-left font-medium">Sort order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $index => $category)
                            <tr>
                                <td class="admin-table-cell">
                                    <input type="hidden" name="categories[{{ $index }}][id]" value="{{ $category->id }}">
                                    <label class="inline-flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="categories[{{ $index }}][show_in_navbar]"
                                            value="1"
                                            class="rounded border-neutral-300 text-brand-red focus:ring-brand-red"
                                            @checked(old("categories.$index.show_in_navbar", $category->show_in_navbar))
                                        >
                                        <span class="text-sm">Visible</span>
                                    </label>
                                </td>
                                <td class="admin-table-cell font-medium">{{ $category->name }}</td>
                                <td class="admin-table-cell">
                                    <input
                                        type="number"
                                        name="categories[{{ $index }}][navbar_sort_order]"
                                        value="{{ old("categories.$index.navbar_sort_order", $category->navbar_sort_order) }}"
                                        min="0"
                                        max="9999"
                                        class="input-field w-24"
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-primary w-full sm:w-auto">Save navbar categories</button>
            </div>
        @endif
    </form>
@endsection
