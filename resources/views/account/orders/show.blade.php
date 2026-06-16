@extends('account.layout')

@section('title', 'Order ' . $order->order_number . ' - SACYSHOES')
@section('account-heading', 'Order ' . $order->order_number)
@section('account-subheading', 'Placed on ' . $order->created_at->format('M j, Y'))

@section('account-content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <h2 class="page-heading">Order Tracking</h2>
                <div class="mt-6">
                    @include('account.partials.tracking-timeline', ['steps' => $trackingSteps])
                </div>
            </div>

            <div class="card p-6">
                <h2 class="page-heading">Items</h2>
                <div class="mt-4 divide-y divide-neutral-100">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between gap-4 py-4 text-sm">
                            <div>
                                <p class="font-medium uppercase tracking-wide">{{ $item->product_name }}</p>
                                <p class="mt-1 text-brand-muted">
                                    SKU: {{ $item->product_sku }} · Qty: {{ $item->quantity }}
                                </p>
                            </div>
                            <p class="font-medium">
                                {{ config('shop.currency_symbol') }} {{ number_format($item->total_price, 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card p-6">
                <h2 class="page-heading">Delivery Details</h2>
                <dl class="mt-4 space-y-3 text-sm">
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
                        <dd class="font-medium">
                            {{ $order->billing_address }}, {{ $order->billing_city }}, {{ $order->billing_country }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="page-heading">Summary</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-brand-muted">Subtotal</dt>
                        <dd>{{ config('shop.currency_symbol') }} {{ number_format($order->subtotal, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-brand-muted">Delivery</dt>
                        <dd>{{ config('shop.currency_symbol') }} {{ number_format($order->delivery_fee, 2) }}</dd>
                    </div>
                    @if ($order->tax > 0)
                        <div class="flex justify-between">
                            <dt class="text-brand-muted">Tax</dt>
                            <dd>{{ config('shop.currency_symbol') }} {{ number_format($order->tax, 2) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-neutral-200 pt-3 font-semibold">
                        <dt>Total</dt>
                        <dd class="text-brand-red">
                            {{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="page-heading">Status</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div>
                        <dt class="text-brand-muted">Order</dt>
                        <dd class="font-medium">{{ $order->status->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-brand-muted">Payment</dt>
                        <dd class="font-medium">{{ $order->payment_status->label() }}</dd>
                    </div>
                </dl>

                @if ($order->payment_status->value !== 'paid')
                    <a href="{{ route('paystack.initialize', $order) }}" class="btn-primary mt-6 block w-full py-2.5 text-center">
                        Pay with Paystack
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
