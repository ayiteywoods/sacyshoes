@extends('layouts.storefront')

@section('title', $product->name.' - SACYSHOES')

@section('content')
    @include('storefront.partials.product-hero')

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="product-detail-layout">
            <div class="product-detail-gallery card p-4 sm:p-5">
                <x-product-gallery :product="$product" />
            </div>

            <div class="product-detail-purchase card p-6 sm:p-8">
                @if ($product->discount_price)
                    <span class="inline-block rounded-none bg-brand-red px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Sale</span>
                @endif

                <div class="mt-2 flex items-start justify-between gap-4">
                    <h1 class="min-w-0 flex-1 text-2xl font-semibold uppercase tracking-wide sm:text-3xl">{{ $product->name }}</h1>
                    <div class="flex shrink-0 items-center gap-2">
                        <x-product-favorite-button :product="$product" />
                        {{-- Share disabled for now
                        <x-product-share-button :product="$product" label />
                        --}}
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <span class="text-3xl font-semibold text-brand-red">{{ config('shop.currency_symbol') }} {{ number_format($product->sellingPrice(), 2) }}</span>
                    @if ($product->discount_price)
                        <span class="text-lg text-brand-muted line-through">{{ config('shop.currency_symbol') }} {{ number_format($product->price, 2) }}</span>
                    @endif
                </div>

                <p class="mt-6 leading-relaxed text-neutral-600">{{ $product->description }}</p>

                @if ($product->variants->isNotEmpty())
                    <form action="{{ route('cart.store') }}" method="POST" class="mt-8 space-y-6 border-t border-neutral-200 pt-8">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        @if ($errors->any())
                            <div class="border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-900">
                                <ul class="space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <x-product-variant-picker :product="$product" />

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-3">
                                <label for="quantity" class="text-sm uppercase tracking-wide text-brand-muted">Qty</label>
                                <input
                                    id="quantity"
                                    type="number"
                                    name="quantity"
                                    min="1"
                                    max="1"
                                    value="1"
                                    class="input-field w-20"
                                    disabled
                                >
                            </div>
                            <button
                                id="add-to-cart"
                                type="submit"
                                data-out-of-stock="{{ $product->isInStock() ? 'false' : 'true' }}"
                                class="btn-primary w-full py-3 sm:flex-1 disabled:cursor-not-allowed disabled:opacity-50"
                                @disabled(! $product->isInStock())
                            >
                                {{ $product->isInStock() ? 'Add To Cart' : 'Out of Stock' }}
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mt-8 border-t border-neutral-200 pt-8 text-sm text-brand-muted">
                        This product has no size, color, or heel options configured yet.
                    </div>
                @endif

                <div class="mt-8 border-t border-neutral-200 pt-8">
                    <x-product-delivery-info />
                </div>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section class="mt-20 border-t border-neutral-200 pt-16">
                <h2 class="section-title">You may also like</h2>
                <div class="mt-8 grid grid-cols-2 gap-5 lg:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        @include('storefront.partials.product-card', ['product' => $related])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
