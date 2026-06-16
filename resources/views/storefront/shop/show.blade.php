@extends('layouts.storefront')

@section('title', $product->name.' - SACYSHOES')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-8 text-sm text-brand-muted">
            <a href="{{ route('home') }}" class="transition hover:text-brand-red">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('shop.index') }}" class="transition hover:text-brand-red">Shop</a>
            <span class="mx-2">/</span>
            <span class="text-brand-black">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2">
            <div class="space-y-4">
                @forelse ($product->images as $image)
                    <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $product->name }}" class="card w-full object-cover">
                @empty
                    <div class="card flex aspect-square items-center justify-center bg-neutral-100 text-brand-muted">No image available</div>
                @endforelse
            </div>

            <div>
                @if ($product->discount_price)
                    <span class="inline-block rounded-none bg-brand-red px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Sale</span>
                @endif

                <p class="mt-2 text-sm uppercase tracking-wide text-brand-red">{{ $product->category->name }}</p>
                <h1 class="mt-1 text-3xl font-semibold uppercase tracking-wide">{{ $product->name }}</h1>
                <p class="mt-1 text-sm text-brand-muted">SKU: {{ $product->sku }}</p>

                <div class="mt-6 flex items-center gap-3">
                    <span class="text-3xl font-semibold text-brand-red">{{ config('shop.currency_symbol') }} {{ number_format($product->sellingPrice(), 2) }}</span>
                    @if ($product->discount_price)
                        <span class="text-lg text-brand-muted line-through">{{ config('shop.currency_symbol') }} {{ number_format($product->price, 2) }}</span>
                    @endif
                </div>

                <p class="mt-6 leading-relaxed text-neutral-600">{{ $product->description }}</p>

                <div class="mt-6 border border-neutral-200 bg-brand-light p-4 text-sm">
                    @if ($product->isInStock())
                        <span class="font-medium text-brand-black">In stock</span>
                        <span class="text-brand-muted">({{ $product->quantity }} available)</span>
                    @else
                        <span class="font-medium text-brand-red">Out of stock</span>
                    @endif
                </div>

                <form action="{{ route('cart.store') }}" method="POST" class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="flex items-center gap-3">
                        <label for="quantity" class="text-sm uppercase tracking-wide text-brand-muted">Qty</label>
                        <input
                            id="quantity"
                            type="number"
                            name="quantity"
                            min="1"
                            max="{{ $product->quantity }}"
                            value="1"
                            class="input-field w-20"
                            @disabled(! $product->isInStock())
                        >
                    </div>
                    <button
                        type="submit"
                        class="btn-primary w-full py-3 sm:w-auto sm:px-10 {{ $product->isInStock() ? '' : 'cursor-not-allowed opacity-50' }}"
                        @disabled(! $product->isInStock())
                    >
                        {{ $product->isInStock() ? 'Add To Cart' : 'Out of Stock' }}
                    </button>
                </form>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section class="mt-20 border-t border-neutral-200 pt-16">
                <h2 class="section-title">You may also like</h2>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        @include('storefront.partials.product-card', ['product' => $related])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
