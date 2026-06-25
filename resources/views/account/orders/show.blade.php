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
                                @if ($item->optionLabel())
                                    <p class="mt-1 text-brand-muted">{{ $item->optionLabel() }}</p>
                                @endif
                                <p class="mt-1 text-brand-muted">
                                    SKU: {{ $item->variant_sku ?? $item->product_sku }} · Qty: {{ $item->quantity }}
                                </p>
                            </div>
                            <p class="font-medium">
                                {{ config('shop.currency_symbol') }} {{ number_format($item->total_price, 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="card p-6">
                    <h2 class="page-heading">Billing Details</h2>
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

                <div class="card p-6">
                    <h2 class="page-heading">Shipping Details</h2>
                    <div class="mt-4">
                        @include('partials.order-shipping-details', ['order' => $order])
                    </div>
                </div>
            </div>

            @if ($order->payment_status->value === 'paid')
                <div class="card p-6">
                    <h2 class="page-heading">Payment Details</h2>
                    <div class="mt-4">
                        @include('partials.order-payment-details', ['order' => $order])
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="page-heading">Summary</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-brand-muted">Subtotal</dt>
                        <dd>{{ config('shop.currency_symbol') }} {{ number_format($order->subtotal, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-brand-muted shrink-0">Delivery fee</dt>
                        <dd class="text-right">{{ $order->deliveryFeeLabel() }}</dd>
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
                    <a href="{{ \App\Support\GuestOrderAccess::paystackInitializeUrl($order) }}" class="btn-primary mt-6 block w-full py-2.5 text-center">
                        Pay with Paystack
                    </a>
                @else
                    <div class="mt-6 flex flex-col gap-2">
                        <a href="{{ route('orders.invoice', $order) }}" class="btn-primary block w-full py-2.5 text-center" target="_blank" rel="noopener">
                            View Invoice
                        </a>
                        <a href="{{ route('orders.invoice.pdf', $order) }}" class="btn-outline block w-full py-2.5 text-center">
                            Download PDF
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
