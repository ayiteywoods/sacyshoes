@extends('layouts.storefront')

@section('title', 'Your Cart - SACYSHOES')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="section-eyebrow">Your Bag</p>
                <h1 class="section-title mt-1">Shopping Cart</h1>
                <p class="mt-2 text-brand-muted">{{ $items->count() }} {{ Str::plural('item', $items->count()) }} in your cart</p>
            </div>
            <a href="{{ route('shop.index') }}" class="text-sm font-medium text-brand-red transition hover:underline">
                <span class="inline-flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Continue Shopping</span>
                </span>
            </a>
        </div>

        @if ($items->isEmpty())
            <div class="mt-12 border border-neutral-200 bg-brand-white p-10 text-center">
                <p class="text-brand-muted">Your cart is empty.</p>
                <a href="{{ route('shop.index') }}" class="btn-primary mt-6 inline-flex px-8 py-3">Browse Products</a>
            </div>
        @else
            <div class="mt-10 grid gap-10 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    @foreach ($items as $item)
                        <div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                            <a href="{{ route('shop.show', $item->product) }}" class="shrink-0">
                                @if ($item->product->primaryImage())
                                    <img
                                        src="{{ asset('storage/'.$item->product->primaryImage()->path) }}"
                                        alt="{{ $item->product->name }}"
                                        class="h-28 w-28 object-cover"
                                    >
                                @else
                                    <div class="flex h-28 w-28 items-center justify-center bg-neutral-100 text-xs text-brand-muted">No image</div>
                                @endif
                            </a>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs uppercase tracking-wide text-brand-red">{{ $item->product->category->name }}</p>
                                <a href="{{ route('shop.show', $item->product) }}" class="mt-1 block font-medium uppercase tracking-wide transition hover:text-brand-red">
                                    {{ $item->product->name }}
                                </a>
                                <p class="mt-1 text-sm text-brand-muted">SKU: {{ $item->product->sku }}</p>
                                <p class="mt-2 font-semibold text-brand-red">
                                    {{ config('shop.currency_symbol') }} {{ number_format($item->unit_price, 2) }}
                                </p>
                            </div>

                            <div class="flex flex-col items-start gap-3 sm:items-end">
                                <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label for="quantity-{{ $item->id }}" class="sr-only">Quantity</label>
                                    <input
                                        id="quantity-{{ $item->id }}"
                                        type="number"
                                        name="quantity"
                                        min="1"
                                        max="{{ $item->product->quantity }}"
                                        value="{{ $item->quantity }}"
                                        class="input-field w-20"
                                    >
                                    <button type="submit" class="btn-outline px-3 py-2" aria-label="Update quantity">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            <span>Update</span>
                                        </span>
                                    </button>
                                </form>

                                <p class="text-sm font-medium">
                                    {{ config('shop.currency_symbol') }} {{ number_format($item->lineTotal(), 2) }}
                                </p>

                                <form action="{{ route('cart.destroy', $item) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs uppercase tracking-wide text-brand-red transition hover:underline" aria-label="Remove item">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        <span>Remove</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <form action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs uppercase tracking-wide text-brand-muted transition hover:text-brand-red">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18.75A2.25 2.25 0 008.25 21h7.5A2.25 2.25 0 0018 18.75V7.5H6v11.25zM9 7.5V6a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 6v1.5M4.5 7.5h15" />
                            </svg>
                            <span>Clear Cart</span>
                        </button>
                    </form>
                </div>

                <aside class="card h-fit p-6">
                    <h2 class="page-heading">Order Summary</h2>
                    <div class="mt-6 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-brand-muted">Subtotal</span>
                            <span class="font-medium">{{ config('shop.currency_symbol') }} {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-brand-muted">Delivery Fee</span>
                            <span>
                                @if ($totals['delivery_fee'] > 0)
                                    {{ config('shop.currency_symbol') }} {{ number_format($totals['delivery_fee'], 2) }}
                                @else
                                    <span class="text-brand-red">Free</span>
                                @endif
                            </span>
                        </div>
                        @if ($totals['tax'] > 0)
                            <div class="flex items-center justify-between">
                                <span class="text-brand-muted">Tax</span>
                                <span>{{ config('shop.currency_symbol') }} {{ number_format($totals['tax'], 2) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between border-t border-neutral-200 pt-3 text-base">
                            <span class="font-medium">Total</span>
                            <span class="font-semibold text-brand-red">{{ config('shop.currency_symbol') }} {{ number_format($totals['total'], 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.create') }}" class="btn-primary mt-6 flex w-full items-center justify-center gap-2 py-3">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.374 3.374 0 01-1.043 3.296 3.374 3.374 0 00-1.048 2.859 3.374 3.374 0 01-1.85 3.135 3.374 3.374 0 00-1.566-.878M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ auth()->check() ? 'Proceed to Checkout' : 'Login to Checkout' }}</span>
                    </a>
                </aside>
            </div>
        @endif
    </div>
@endsection
