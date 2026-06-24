@extends('layouts.admin')

@section('heading', 'Order '.$order->order_number)

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div id="delivery-tracking" class="card p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="font-semibold">Delivery tracking</h2>
                        <p class="mt-1 text-sm text-brand-muted">
                            @if ($order->isTrackable())
                                Update where this order is in the delivery journey. Customers see the same progress on their account.
                            @elseif ($order->payment_status === \App\Enums\PaymentStatus::Paid)
                                This order is {{ strtolower($order->status->label()) }}. Tracking updates are no longer available.
                            @else
                                Delivery tracking becomes available after the customer pays for this order.
                            @endif
                        </p>
                    </div>
                    @if ($order->isTrackable())
                        <span class="inline-flex shrink-0 items-center self-start bg-brand-red/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-brand-red">
                            {{ $order->status->label() }}
                        </span>
                    @endif
                </div>

                @if ($order->payment_status === \App\Enums\PaymentStatus::Paid)
                    <div class="mt-6 grid gap-8 lg:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Customer view</p>
                            <div class="mt-4">
                                @include('account.partials.tracking-timeline', ['steps' => $trackingSteps])
                            </div>
                        </div>

                        @if ($order->isTrackable())
                            <div class="border-t border-neutral-200 pt-6 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-8">
                                @include('admin.partials.order-tracking-form', ['order' => $order])
                            </div>
                        @endif
                    </div>
                @else
                    <div class="mt-6 border border-neutral-200 bg-brand-light p-4">
                        @include('admin.partials.order-tracking-form', ['order' => $order])
                    </div>
                @endif
            </div>

            <div class="card p-6">
                <h2 class="font-semibold">Order items</h2>
                <div class="mt-4 divide-y divide-neutral-100">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between py-3 text-sm">
                            <div>
                                <p class="font-medium">{{ $item->product_name }}</p>
                                @if ($item->optionLabel())
                                    <p class="text-brand-muted">{{ $item->optionLabel() }}</p>
                                @endif
                                <p class="text-brand-muted">SKU: {{ $item->variant_sku ?? $item->product_sku }} x {{ $item->quantity }}</p>
                            </div>
                            <p>{{ config('shop.currency_symbol') }} {{ number_format($item->total_price, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="card p-6">
                    <h2 class="font-semibold">Billing details</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div><dt class="text-brand-muted">Name</dt><dd>{{ $order->billing_full_name }}</dd></div>
                        <div><dt class="text-brand-muted">Email</dt><dd>{{ $order->billing_email }}</dd></div>
                        <div><dt class="text-brand-muted">Phone</dt><dd>{{ $order->billing_phone }}</dd></div>
                        <div><dt class="text-brand-muted">Address</dt><dd>{{ $order->billing_address }}, {{ $order->billing_city }}, {{ $order->billing_country }}</dd></div>
                    </dl>
                </div>

                <div class="card p-6">
                    <h2 class="font-semibold">Shipping details</h2>
                    <div class="mt-4">
                        @include('partials.order-shipping-details', ['order' => $order])
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="font-semibold">Summary</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ config('shop.currency_symbol') }} {{ number_format($order->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between gap-4">
                        <dt class="shrink-0">Delivery fee</dt>
                        <dd class="text-right">{{ $order->deliveryFeeLabel() }}</dd>
                    </div>
                    @if ($order->tax > 0)
                        <div class="flex justify-between"><dt>Tax</dt><dd>{{ config('shop.currency_symbol') }} {{ number_format($order->tax, 2) }}</dd></div>
                    @endif
                    <div class="flex justify-between border-t border-neutral-200 pt-2 font-semibold"><dt>Total</dt><dd>{{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}</dd></div>
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="font-semibold">Status</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-brand-muted">Order</dt>
                        <dd class="font-medium">{{ $order->status->label() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-brand-muted">Payment</dt>
                        <dd class="font-medium">{{ $order->payment_status->label() }}</dd>
                    </div>
                    @if ($order->paid_at)
                        <div class="flex justify-between">
                            <dt class="text-brand-muted">Paid at</dt>
                            <dd class="font-medium">{{ $order->paid_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="card p-6">
                <h2 class="font-semibold">Payment details</h2>
                <div class="mt-4">
                    @include('partials.order-payment-details', ['order' => $order])
                </div>
                @include('admin.partials.order-invoice-actions', ['order' => $order, 'class' => 'mt-6'])
            </div>
        </div>
    </div>
@endsection
