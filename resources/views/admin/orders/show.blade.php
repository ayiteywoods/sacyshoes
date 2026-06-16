@extends('layouts.admin')

@section('heading', 'Order '.$order->order_number)

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="card p-6">
                <h2 class="font-semibold">Order items</h2>
                <div class="mt-4 divide-y divide-neutral-100">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between py-3 text-sm">
                            <div>
                                <p class="font-medium">{{ $item->product_name }}</p>
                                <p class="text-brand-muted">SKU: {{ $item->product_sku }} x {{ $item->quantity }}</p>
                            </div>
                            <p>GHS {{ number_format($item->total_price, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card p-6">
                <h2 class="font-semibold">Billing details</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div><dt class="text-brand-muted">Name</dt><dd>{{ $order->billing_full_name }}</dd></div>
                    <div><dt class="text-brand-muted">Email</dt><dd>{{ $order->billing_email }}</dd></div>
                    <div><dt class="text-brand-muted">Phone</dt><dd>{{ $order->billing_phone }}</dd></div>
                    <div><dt class="text-brand-muted">Address</dt><dd>{{ $order->billing_address }}, {{ $order->billing_city }}, {{ $order->billing_country }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h2 class="font-semibold">Summary</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><dt>Subtotal</dt><dd>GHS {{ number_format($order->subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Delivery</dt><dd>GHS {{ number_format($order->delivery_fee, 2) }}</dd></div>
                    <div class="flex justify-between"><dt>Tax</dt><dd>GHS {{ number_format($order->tax, 2) }}</dd></div>
                    <div class="flex justify-between border-t border-neutral-200 pt-2 font-semibold"><dt>Total</dt><dd>GHS {{ number_format($order->total, 2) }}</dd></div>
                </dl>
            </div>

            <div id="order-status" class="card p-6">
                <h2 class="font-semibold">Update status</h2>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="input-field">
                        @foreach (\App\Enums\OrderStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
@endsection
