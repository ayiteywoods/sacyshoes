@extends('layouts.storefront')

@section('title', 'Checkout - SACYSHOES')

@section('content')
    @include('storefront.partials.checkout-hero')

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @php
            $regionsPayload = $regions
                ->map(fn ($region) => [
                    'id' => $region->id,
                    'name' => $region->name,
                    'is_accra' => (bool) $region->is_accra,
                    'options' => $region->options
                        ->where('is_active', true)
                        ->sortBy('sort_order')
                        ->map(fn ($option) => [
                            'id' => $option->id,
                            'name' => $option->name,
                            'price' => (float) $option->price,
                            'description' => $option->description,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all();
        @endphp

        @php
            $billingSameAsShipping = old('billing_same_as_shipping', '1') === '1'
                || old('billing_same_as_shipping') === true
                || old('billing_same_as_shipping') === 1;
        @endphp

        <div
            class="grid gap-10 lg:grid-cols-3"
            x-data="checkoutShipping(@js($regionsPayload), {{ (int) old('shipping_region_id', $defaultRegionId) }}, {{ (int) old('shipping_option_id', 0) }}, {{ (float) $totals['subtotal'] }}, {{ (float) $totals['tax'] }}, @js($billingSameAsShipping), {{ (float) $totals['discount'] }})"
        >
            <div class="lg:col-span-2">
                <form
                    action="{{ route('checkout.store') }}"
                    method="POST"
                    class="card p-6"
                    @submit="syncBillingFromShipping()"
                >
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-900">
                            <p class="font-medium">Please fix the following before placing your order:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h2 class="page-heading">Shipping Information</h2>
                    <p class="mt-2 text-xs text-brand-muted">Fields marked with <span class="text-brand-red">*</span> are required.</p>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-form-label for="shipping_full_name" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Full Name</x-form-label>
                            <input
                                id="shipping_full_name"
                                type="text"
                                name="shipping_full_name"
                                value="{{ old('shipping_full_name', old('billing_full_name', $billing['billing_full_name'] ?? '')) }}"
                                required
                                class="input-field"
                            >
                            @error('shipping_full_name')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="shipping_phone" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Phone Number</x-form-label>
                            <input
                                id="shipping_phone"
                                type="text"
                                name="shipping_phone"
                                value="{{ old('shipping_phone', old('billing_phone', $billing['billing_phone'] ?? '')) }}"
                                required
                                class="input-field"
                            >
                            @error('shipping_phone')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="shipping_email" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Email</x-form-label>
                            <input
                                id="shipping_email"
                                type="email"
                                name="shipping_email"
                                value="{{ old('shipping_email', old('billing_email', $billing['billing_email'] ?? '')) }}"
                                required
                                class="input-field"
                            >
                            @error('shipping_email')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="shipping_country" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Country</x-form-label>
                            <input
                                id="shipping_country"
                                type="text"
                                name="shipping_country"
                                value="{{ old('shipping_country', config('shop.default_country')) }}"
                                required
                                class="input-field"
                            >
                            @error('shipping_country')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="shipping_region_id" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Region</x-form-label>
                            <select
                                id="shipping_region_id"
                                name="shipping_region_id"
                                required
                                class="input-field"
                                x-model.number="regionId"
                                @change="onRegionChange()"
                            >
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                            @error('shipping_region_id')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="shipping_city" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">City</x-form-label>
                            <input
                                id="shipping_city"
                                type="text"
                                name="shipping_city"
                                value="{{ old('shipping_city', old('billing_city', $billing['billing_city'] ?? '')) }}"
                                required
                                class="input-field"
                            >
                            @error('shipping_city')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="shipping_address" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Address</x-form-label>
                            <input
                                id="shipping_address"
                                type="text"
                                name="shipping_address"
                                value="{{ old('shipping_address', old('billing_address', $billing['billing_address'] ?? '')) }}"
                                required
                                class="input-field"
                            >
                            @error('shipping_address')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2" x-show="isAccra" x-cloak>
                            <div class="border border-neutral-200 bg-brand-light p-4 text-sm text-brand-muted">
                                Accra delivery: payment is made directly to the delivery person upon arrival.
                            </div>

                            <div class="mt-4">
                                <label for="customer_comment" class="text-xs uppercase tracking-wide text-brand-muted">Add Note</label>
                                <textarea
                                    id="customer_comment"
                                    name="customer_comment"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Add delivery instructions, landmarks, or any notes for the rider."
                                    class="input-field mt-1"
                                    :disabled="!isAccra"
                                >{{ old('customer_comment') }}</textarea>
                                @error('customer_comment')
                                    <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="sm:col-span-2" x-show="!isAccra" x-cloak>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Delivery options<span class="text-brand-red" x-show="!isAccra" x-cloak> *</span></p>
                            <div class="mt-3 space-y-2">
                                <template x-for="option in options" :key="option.id">
                                    <label class="flex cursor-pointer items-start gap-3 border border-neutral-200 bg-white p-4 text-sm transition hover:border-brand-red">
                                        <input
                                            type="radio"
                                            name="shipping_option_id"
                                            class="mt-1 rounded-none border-neutral-300 text-brand-red focus:ring-brand-red"
                                            :value="option.id"
                                            x-model.number="optionId"
                                            @change="recalculateTotals()"
                                        >
                                        <span class="min-w-0 flex-1">
                                            <span class="flex items-center justify-between gap-3">
                                                <span class="font-medium text-brand-black" x-text="option.name"></span>
                                                <span class="shrink-0 font-medium text-brand-black">
                                                    {{ config('shop.currency_symbol') }}
                                                    <span x-text="option.price.toFixed(2)"></span>
                                                </span>
                                            </span>
                                            <span class="mt-1 block text-xs text-brand-muted" x-show="option.description" x-text="option.description"></span>
                                        </span>
                                    </label>
                                </template>
                            </div>
                            @error('shipping_option_id')
                                <p class="mt-2 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-10 border-t border-neutral-200 pt-8">
                        <label class="flex cursor-pointer items-center gap-3 text-sm text-brand-black">
                            <input
                                type="checkbox"
                                x-model="sameAsShipping"
                                class="rounded-none border-neutral-300 text-brand-red focus:ring-brand-red"
                            >
                            <span>Billing information is the same as shipping</span>
                        </label>
                        <input type="hidden" name="billing_same_as_shipping" value="{{ $billingSameAsShipping ? 1 : 0 }}" x-bind:value="sameAsShipping ? 1 : 0">
                    </div>

                    <div x-show="!sameAsShipping" x-cloak>
                        <h2 class="page-heading mt-8">Billing Information</h2>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-form-label for="billing_full_name" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Full Name</x-form-label>
                            <input
                                id="billing_full_name"
                                type="text"
                                name="billing_full_name"
                                value="{{ old('billing_full_name', $billing['billing_full_name'] ?? '') }}"
                                :required="!sameAsShipping"
                                class="input-field"
                            >
                            @error('billing_full_name')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="billing_phone" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Phone Number</x-form-label>
                            <input
                                id="billing_phone"
                                type="text"
                                name="billing_phone"
                                value="{{ old('billing_phone', $billing['billing_phone'] ?? '') }}"
                                :required="!sameAsShipping"
                                class="input-field"
                            >
                            @error('billing_phone')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="billing_email" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Email</x-form-label>
                            <input
                                id="billing_email"
                                type="email"
                                name="billing_email"
                                value="{{ old('billing_email', $billing['billing_email'] ?? '') }}"
                                :required="!sameAsShipping"
                                class="input-field"
                            >
                            @error('billing_email')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="billing_country" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Country</x-form-label>
                            <input
                                id="billing_country"
                                type="text"
                                name="billing_country"
                                value="{{ old('billing_country', $billing['billing_country'] ?? config('shop.default_country')) }}"
                                :required="!sameAsShipping"
                                class="input-field"
                            >
                            @error('billing_country')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="billing_city" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">City</x-form-label>
                            <input
                                id="billing_city"
                                type="text"
                                name="billing_city"
                                value="{{ old('billing_city', $billing['billing_city'] ?? '') }}"
                                :required="!sameAsShipping"
                                class="input-field"
                            >
                            @error('billing_city')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-form-label for="billing_address" class="text-xs uppercase tracking-wide text-brand-muted" :required="true">Address</x-form-label>
                            <input
                                id="billing_address"
                                type="text"
                                name="billing_address"
                                value="{{ old('billing_address', $billing['billing_address'] ?? '') }}"
                                :required="!sameAsShipping"
                                class="input-field"
                            >
                            @error('billing_address')
                                <p class="mt-1 text-xs text-brand-red">{{ $message }}</p>
                            @enderror
                        </div>
                        </div>
                    </div>

                    @auth
                    <label class="mt-6 flex items-center gap-2 text-sm text-brand-muted">
                        <input type="checkbox" name="save_address" value="1" class="rounded-none border-neutral-300 text-brand-red focus:ring-brand-red" @checked(old('save_address'))>
                        Save this address for future orders
                    </label>
                    @else
                    <p class="mt-6 text-sm text-brand-muted">
                        Checking out as a guest.
                        <a href="{{ route('login') }}" class="font-medium text-brand-red hover:underline">Log in</a>
                        or
                        <a href="{{ route('register') }}" class="font-medium text-brand-red hover:underline">create an account</a>
                        to save your details for next time.
                    </p>
                    @endauth

                    @error('cart')
                        <p class="mt-4 text-sm text-brand-red">{{ $message }}</p>
                    @enderror

                    <div class="mt-8 flex flex-col gap-3">
                        <button type="submit" class="btn-primary w-full px-8 py-3.5">
                            Place Order
                        </button>
                        <a href="{{ route('cart.index') }}" class="text-sm text-brand-muted transition hover:text-brand-red">
                            &larr; Back to Cart
                        </a>
                    </div>
                </form>
            </div>

            <aside class="space-y-6">
                <div class="card h-fit p-6">
                    <h2 class="page-heading">Order Summary</h2>

                    <div class="mt-6 space-y-4">
                        @foreach ($items as $item)
                            <div class="flex items-start justify-between gap-3 border-b border-neutral-100 pb-4 text-sm">
                                <div class="min-w-0">
                                    <p class="font-medium uppercase tracking-wide">{{ $item->product->name }}</p>
                                    @if ($item->optionLabel())
                                        <p class="mt-1 text-brand-muted">{{ $item->optionLabel() }}</p>
                                    @endif
                                    <p class="mt-1 text-brand-muted">Qty: {{ $item->quantity }}</p>
                                </div>
                                <p class="shrink-0 font-medium">
                                    {{ config('shop.currency_symbol') }} {{ number_format($item->lineTotal(), 2) }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 border-t border-neutral-200 pt-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Coupon</p>

                        @if ($appliedCoupon)
                            <div class="mt-3 flex items-start justify-between gap-3 rounded border border-green-200 bg-green-50 px-3 py-3 text-sm">
                                <div class="min-w-0">
                                    <p class="font-medium text-brand-black">{{ $appliedCoupon->code }}</p>
                                    <p class="mt-1 text-brand-muted">{{ app(\App\Services\CouponService::class)->formatSummary($appliedCoupon) }}</p>
                                </div>
                                <form method="POST" action="{{ route('checkout.coupon.remove') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="shrink-0 text-xs font-medium text-brand-red hover:underline">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('checkout.coupon.apply') }}" class="mt-3 flex flex-col gap-2 sm:flex-row">
                                @csrf
                                <input
                                    type="text"
                                    name="coupon_code"
                                    value="{{ old('coupon_code') }}"
                                    placeholder="Enter coupon code"
                                    class="input-field flex-1 uppercase"
                                    autocomplete="off"
                                >
                                <button type="submit" class="btn-outline px-4 py-2.5 sm:shrink-0">
                                    Apply
                                </button>
                            </form>
                        @endif

                        @error('coupon_code')
                            <p class="mt-2 text-xs text-brand-red">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-brand-muted">Subtotal</span>
                            <span>{{ config('shop.currency_symbol') }} {{ number_format($totals['subtotal'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between" x-show="discount > 0" x-cloak>
                            <span class="text-brand-muted">Discount</span>
                            <span class="text-brand-red">-<span x-text="discountLabel"></span></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-brand-muted">Delivery Fee</span>
                            <span>
                                    <span x-text="deliveryLabel"></span>
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
                                    <span x-text="totalLabel"></span>
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
                </div>

                @include('storefront.partials.checkout-payment')
            </aside>
        </div>
    </div>
@endsection
