@extends('layouts.storefront')

@section('title', 'Order Placed - SACYSHOES')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="card p-8 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-brand-red/10 text-brand-red">
                <svg width="28" height="28" class="h-7 w-7 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <p class="section-eyebrow mt-6">Order Received</p>
            <h1 class="section-title mt-2">Thank You For Your Order</h1>
            <p class="mt-3 text-brand-muted">
                Order <span class="font-medium text-brand-black">{{ $order->order_number }}</span> has been created and is awaiting payment.
            </p>
        </div>

        <div class="card mt-8 p-6">
            <h2 class="page-heading">Order Summary</h2>

            <div class="mt-6 space-y-4">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between gap-4 border-b border-neutral-100 pb-4 text-sm">
                        <div>
                            <p class="font-medium uppercase tracking-wide">{{ $item->product_name }}</p>
                            <p class="mt-1 text-brand-muted">Qty: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-medium">
                            {{ config('shop.currency_symbol') }} {{ number_format($item->total_price, 2) }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-brand-muted">Subtotal</span>
                    <span>{{ config('shop.currency_symbol') }} {{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-brand-muted">Delivery</span>
                    <span>{{ config('shop.currency_symbol') }} {{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                @if ($order->tax > 0)
                    <div class="flex justify-between">
                        <span class="text-brand-muted">Tax</span>
                        <span>{{ config('shop.currency_symbol') }} {{ number_format($order->tax, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-neutral-200 pt-3 text-base font-semibold">
                    <span>Total</span>
                    <span class="text-brand-red">{{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <div class="mt-6 border border-neutral-200 bg-brand-light p-4 text-sm">
                <p class="font-medium text-brand-black">Status: {{ $order->status->label() }}</p>
                <p class="mt-1 text-brand-muted">Payment: {{ $order->payment_status->label() }}</p>
            </div>
        </div>

        <div class="card mt-6 p-6">
            <h2 class="page-heading">Delivery Details</h2>
            <dl class="mt-4 space-y-2 text-sm">
                <div>
                    <dt class="text-brand-muted">Name</dt>
                    <dd class="font-medium">{{ $order->billing_full_name }}</dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Phone</dt>
                    <dd class="font-medium">{{ $order->billing_phone }}</dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Email</dt>
                    <dd class="font-medium">{{ $order->billing_email }}</dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Address</dt>
                    <dd class="font-medium">{{ $order->billing_address }}, {{ $order->billing_city }}, {{ $order->billing_country }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            @if ($order->payment_status->value === 'paid')
                <span class="btn-primary cursor-default px-8 py-3 text-center">
                    Payment Completed
                </span>
            @else
                <a href="{{ route('paystack.initialize', $order) }}" class="btn-primary px-8 py-3 text-center">
                    Pay with Paystack
                </a>
            @endif
            <a href="{{ route('account.orders.show', $order) }}" class="btn-outline px-8 py-3 text-center">
                View Order
            </a>
            <a href="{{ route('shop.index') }}" class="btn-outline px-8 py-3 text-center">
                Continue Shopping
            </a>
        </div>
    </div>
@endsection
