@extends('layouts.storefront')

@section('title', 'Shop - SACYSHOES')

@section('content')
    <div class="border-b border-neutral-200 bg-brand-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <p class="section-eyebrow">Our Collection</p>
            <h1 class="section-title mt-1">
                @if (request()->filled('q'))
                    Results for &ldquo;{{ request('q') }}&rdquo;
                @else
                    Shop All
                @endif
            </h1>
            <p class="mt-2 text-brand-muted">{{ $products->total() }} products {{ request()->filled('q') ? 'found' : 'available' }}</p>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
            <aside class="card h-fit p-6">
                <h2 class="font-semibold">Filters</h2>
                <form method="GET" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium">Search</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Name or SKU" class="input-field">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Category</label>
                        <select name="category" class="input-field">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                            @endforeach
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
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($products as $product)
                        @include('storefront.partials.product-card', ['product' => $product])
                    @empty
                        <div class="col-span-full py-16 text-center">
                            <p class="text-lg font-medium text-brand-black">No products found</p>
                            <p class="mt-1 text-brand-muted">Try adjusting your filters.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
