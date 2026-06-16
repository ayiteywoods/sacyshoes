@extends('layouts.storefront')

@section('title', 'Checkout - SACYSHOES')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-10">
            <p class="section-eyebrow">Secure Checkout</p>
            <h1 class="section-title mt-1">Complete Your Order</h1>
            <p class="mt-2 text-brand-muted">Enter your billing details to place your order.</p>
        </div>

        <div class="grid gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <form action="{{ route('checkout.store') }}" method="POST" class="card p-6">
                    @csrf

                    <h2 class="page-heading">Billing Information</h2>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="billing_full_name" class="text-xs uppercase tracking-wide text-brand-muted">Full Name</label>
                            <input
                                id="billing_full_name"
                                type="text"
                                name="billing_full_name"
                                value="{{ old('billing_full_name', $billing['billing_full_name'] ?? '') }}"
                                required
                                class="input-field"
                            >
                            @error('billing_full_name')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_phone" class="text-xs uppercase tracking-wide text-brand-muted">Phone Number</label>
                            <input
                                id="billing_phone"
                                type="text"
                                name="billing_phone"
                                value="{{ old('billing_phone', $billing['billing_phone'] ?? '') }}"
                                required
                                class="input-field"
                            >
                            @error('billing_phone')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_email" class="text-xs uppercase tracking-wide text-brand-muted">Email</label>
                            <input
                                id="billing_email"
                                type="email"
                                name="billing_email"
                                value="{{ old('billing_email', $billing['billing_email'] ?? '') }}"
                                required
                                class="input-field"
                            >
                            @error('billing_email')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="billing_address" class="text-xs uppercase tracking-wide text-brand-muted">Address</label>
                            <input
                                id="billing_address"
                                type="text"
                                name="billing_address"
                                value="{{ old('billing_address', $billing['billing_address'] ?? '') }}"
                                required
                                class="input-field"
                            >
                            @error('billing_address')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_city" class="text-xs uppercase tracking-wide text-brand-muted">City</label>
                            <input
                                id="billing_city"
                                type="text"
                                name="billing_city"
                                value="{{ old('billing_city', $billing['billing_city'] ?? '') }}"
                                required
                                class="input-field"
                            >
                            @error('billing_city')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_country" class="text-xs uppercase tracking-wide text-brand-muted">Country</label>
                            <input
                                id="billing_country"
                                type="text"
                                name="billing_country"
                                value="{{ old('billing_country', $billing['billing_country'] ?? config('shop.default_country')) }}"
                                required
                                class="input-field"
                            >
                            @error('billing_country')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <label class="mt-6 flex items-center gap-2 text-sm text-brand-muted">
                        <input type="checkbox" name="save_address" value="1" class="rounded-none border-neutral-300 text-brand-red focus:ring-brand-red" @checked(old('save_address'))>
                        Save this address for future orders
                    </label>

                    @error('cart')
                        <p class="mt-4 text-sm text-brand-red">{{ $message }}</p>
                    @enderror

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button type="submit" class="btn-primary w-full px-8 py-3 sm:w-auto">
                            Place Order
                        </button>
                        <a href="{{ route('cart.index') }}" class="text-sm text-brand-muted transition hover:text-brand-red">
                            &larr; Back to Cart
                        </a>
                    </div>
                </form>
            </div>

            <aside class="card h-fit p-6">
                <h2 class="page-heading">Order Summary</h2>

                <div class="mt-6 space-y-4">
                    @foreach ($items as $item)
                        <div class="flex items-start justify-between gap-3 border-b border-neutral-100 pb-4 text-sm">
                            <div class="min-w-0">
                                <p class="font-medium uppercase tracking-wide">{{ $item->product->name }}</p>
                                <p class="mt-1 text-brand-muted">Qty: {{ $item->quantity }}</p>
                            </div>
                            <p class="shrink-0 font-medium">
                                {{ config('shop.currency_symbol') }} {{ number_format($item->lineTotal(), 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-brand-muted">Subtotal</span>
                        <span>{{ config('shop.currency_symbol') }} {{ number_format($totals['subtotal'], 2) }}</span>
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
                        <span class="font-medium">Grand Total</span>
                        <span class="font-semibold text-brand-red">
                            {{ config('shop.currency_symbol') }} {{ number_format($totals['total'], 2) }}
                        </span>
                    </div>
                </div>

                @if ($totals['delivery_fee'] === 0.0 && $totals['subtotal'] >= config('shop.free_delivery_threshold'))
                    <p class="mt-4 text-xs text-brand-muted">
                        You qualify for free delivery on this order.
                    </p>
                @elseif ($totals['subtotal'] < config('shop.free_delivery_threshold'))
                    <p class="mt-4 text-xs text-brand-muted">
                        Free delivery on orders over {{ config('shop.currency_symbol') }} {{ number_format(config('shop.free_delivery_threshold'), 0) }}.
                    </p>
                @endif
            </aside>
        </div>
    </div>
@endsection
