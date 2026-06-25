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
                Order <span class="font-medium text-brand-black">{{ $order->order_number }}</span>
                @if ($order->status === \App\Enums\OrderStatus::Cancelled)
                    was cancelled because payment was not received in time.
                @elseif ($order->payment_status->value === 'paid')
                    is confirmed. We are preparing it for delivery.
                @else
                    has been created and is awaiting payment.
                @endif
            </p>
            @if ($order->status !== \App\Enums\OrderStatus::Cancelled && $order->payment_status->value !== 'paid' && $order->payment_due_at)
                <p class="mt-2 text-sm text-amber-800">
                    Please complete payment by {{ $order->payment_due_at->format('M j, Y g:i A') }}
                    ({{ config('shop.order_payment_timeout_hours') }} hours from order placement).
                </p>
            @elseif ($order->payment_status->value === 'paid')
                <p class="mt-2 text-sm text-brand-muted">
                    Payment received. You can follow every step of your delivery from your account.
                </p>
            @endif
        </div>

        <x-order-tracking-notice class="mt-8" />

        <div class="card mt-8 p-6">
            <h2 class="page-heading">Order Summary</h2>

            <div class="mt-6 space-y-4">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between gap-4 border-b border-neutral-100 pb-4 text-sm">
                        <div>
                            <p class="font-medium uppercase tracking-wide">{{ $item->product_name }}</p>
                            @if ($item->optionLabel())
                                <p class="mt-1 text-brand-muted">{{ $item->optionLabel() }}</p>
                            @endif
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
                @if ($order->discount_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-brand-muted">Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span>
                        <span class="text-brand-red">-{{ config('shop.currency_symbol') }} {{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
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
                    <dd class="font-medium">{{ $order->shipping_full_name }}</dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Phone</dt>
                    <dd class="font-medium">{{ $order->shipping_phone }}</dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Email</dt>
                    <dd class="font-medium">{{ $order->shipping_email ?: $order->billing_email }}</dd>
                </div>
                <div>
                    <dt class="text-brand-muted">Address</dt>
                    <dd class="font-medium">{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_country }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-8 space-y-4">
            @if ($order->status === \App\Enums\OrderStatus::Cancelled)
                <div class="rounded border border-neutral-200 bg-neutral-50 px-4 py-3 text-center text-sm text-brand-muted">
                    This order was cancelled because payment was not received in time.
                </div>
            @elseif ($order->payment_status->value === 'paid')
                <div class="flex justify-center">
                    <span class="inline-flex items-center gap-2 border border-green-200 bg-green-50 px-4 py-2 text-sm font-medium text-green-800">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Payment completed
                    </span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('orders.invoice', $order) }}" class="btn-primary justify-center px-6 py-3 text-center" target="_blank" rel="noopener">
                        View Invoice
                    </a>
                    <a href="{{ route('orders.invoice.pdf', $order) }}" class="btn-outline justify-center px-6 py-3 text-center">
                        Download PDF
                    </a>
                </div>
            @else
                <a href="{{ \App\Support\GuestOrderAccess::paystackInitializeUrl($order) }}" class="btn-primary flex w-full justify-center px-6 py-3 text-center">
                    Pay with Paystack
                </a>
            @endif

            <div class="grid gap-3 sm:grid-cols-2">
                @auth
                    <a href="{{ route('account.orders.show', $order) }}" class="btn-outline justify-center px-6 py-3 text-center">
                        Track This Order
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-outline justify-center px-6 py-3 text-center">
                        Log In to Track Order
                    </a>
                @endauth
                <a href="{{ route('shop.index') }}" class="btn-outline justify-center px-6 py-3 text-center">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
@endsection
