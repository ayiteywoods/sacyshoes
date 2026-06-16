<dl class="grid gap-4 sm:grid-cols-2">
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Order number</dt>
        <dd class="mt-1 font-medium">{{ $order->order_number }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Customer</dt>
        <dd class="mt-1">{{ $order->user?->name ?? $order->billing_full_name }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Order status</dt>
        <dd class="mt-1">{{ $order->status->label() }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Payment</dt>
        <dd class="mt-1">{{ $order->payment_status->label() }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Placed</dt>
        <dd class="mt-1">{{ $order->created_at->format('M j, Y g:i A') }}</dd>
    </div>
    @if ($order->paid_at)
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Paid at</dt>
            <dd class="mt-1">{{ $order->paid_at->format('M j, Y g:i A') }}</dd>
        </div>
    @endif
</dl>

<div class="mt-6">
    <h3 class="text-sm font-semibold uppercase tracking-wide">Items</h3>
    <table class="mt-3 w-full text-sm">
        <thead>
            <tr class="border-b border-neutral-200 text-left text-xs uppercase tracking-wide text-brand-muted">
                <th class="py-2">Product</th>
                <th class="py-2">Qty</th>
                <th class="py-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100">
            @foreach ($order->items as $item)
                <tr>
                    <td class="py-2">
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-xs text-brand-muted">SKU: {{ $item->product_sku }}</p>
                    </td>
                    <td class="py-2">{{ $item->quantity }}</td>
                    <td class="py-2 text-right">{{ config('shop.currency_symbol') }} {{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6 grid gap-4 sm:grid-cols-2">
    <div>
        <h3 class="text-sm font-semibold uppercase tracking-wide">Billing</h3>
        <dl class="mt-3 space-y-2 text-sm">
            <div><dt class="text-brand-muted">Name</dt><dd>{{ $order->billing_full_name }}</dd></div>
            <div><dt class="text-brand-muted">Email</dt><dd>{{ $order->billing_email }}</dd></div>
            <div><dt class="text-brand-muted">Phone</dt><dd>{{ $order->billing_phone }}</dd></div>
            <div><dt class="text-brand-muted">Address</dt><dd>{{ $order->billing_address }}, {{ $order->billing_city }}, {{ $order->billing_country }}</dd></div>
        </dl>
    </div>
    <div>
        <h3 class="text-sm font-semibold uppercase tracking-wide">Summary</h3>
        <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ config('shop.currency_symbol') }} {{ number_format($order->subtotal, 2) }}</dd></div>
            <div class="flex justify-between"><dt>Delivery</dt><dd>{{ config('shop.currency_symbol') }} {{ number_format($order->delivery_fee, 2) }}</dd></div>
            <div class="flex justify-between"><dt>Tax</dt><dd>{{ config('shop.currency_symbol') }} {{ number_format($order->tax, 2) }}</dd></div>
            <div class="flex justify-between border-t border-neutral-200 pt-2 font-semibold"><dt>Total</dt><dd class="text-brand-red">{{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}</dd></div>
        </dl>
    </div>
</div>
