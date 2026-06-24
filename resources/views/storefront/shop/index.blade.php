@extends('layouts.storefront')

@section('title', 'Shop - SACYSHOES')

@section('content')
    @include('storefront.partials.shop-hero')

    <div
        class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
        x-data="{ filtersOpen: false }"
        @keydown.escape.window="filtersOpen = false"
    >
        <div class="mb-4 flex justify-end lg:hidden" x-show="!filtersOpen">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-none border border-neutral-300 bg-brand-white px-4 py-2.5 text-sm font-medium text-brand-black transition hover:border-brand-red hover:text-brand-red"
                @click.stop="filtersOpen = true"
                :aria-expanded="filtersOpen"
                aria-controls="shop-filters"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0m3 0H3.375a1.125 1.125 0 01-1.125-1.125V4.5m12 6.75h-9.75m9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h3.375a1.125 1.125 0 00-1.125-1.125V12m0 0h9.75" />
                </svg>
                Filters
            </button>
        </div>

        <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
            <aside
                id="shop-filters"
                class="card h-fit p-6"
                :class="filtersOpen ? 'block' : 'hidden lg:block'"
            >
                <div class="mb-4 flex items-center justify-between lg:hidden">
                    <h2 class="font-semibold">Filters</h2>
                    <button
                        type="button"
                        class="rounded-none p-1 text-brand-muted transition hover:text-brand-red"
                        @click="filtersOpen = false"
                        aria-label="Close filters"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <h2 class="hidden font-semibold lg:block">Filters</h2>
                <form method="GET" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Search</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Name or SKU" class="input-field">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Category</label>
                        <select name="category" class="input-field">
                            <option value="">All categories</option>
                            @include('partials.category-select-options', [
                                'categoryTree' => $categoryTree,
                                'selected' => request('category'),
                            ])
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock')) class="text-brand-red focus:ring-brand-red">
                        In stock only
                    </label>
                    <button type="submit" class="btn-primary w-full">Apply Filters</button>
                </form>
            </aside>

            <div>
                <div id="shop-products-grid" class="grid grid-cols-2 gap-5 xl:grid-cols-3">
                    @forelse ($products as $product)
                        @include('storefront.partials.product-card', ['product' => $product])
                    @empty
                        <div class="col-span-full py-16 text-center">
                            <p class="text-lg font-medium text-brand-black">No products found</p>
                            <p class="mt-1 text-brand-muted">Try adjusting your filters.</p>
                        </div>
                    @endforelse
                </div>

                @include('storefront.partials.load-more-button', [
                    'hasMore' => $products->hasMorePages(),
                    'url' => route('shop.index', request()->except('page')),
                    'target' => '#shop-products-grid',
                    'nextPage' => $products->currentPage() + 1,
                ])
            </div>
        </div>
    </div>
@endsection
