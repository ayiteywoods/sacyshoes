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
    @if ($order->payment?->paystackTransactionId())
        <div class="sm:col-span-2">
            <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Paystack reference</dt>
            <dd class="mt-1 break-all font-medium">{{ $order->order_number }}-{{ $order->payment->paystackTransactionId() }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Paystack transaction ID</dt>
            <dd class="mt-1 break-all font-medium">{{ $order->payment->paystackTransactionId() }}</dd>
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
                        @if ($item->optionLabel())
                            <p class="text-xs text-brand-muted">{{ $item->optionLabel() }}</p>
                        @endif
                        <p class="text-xs text-brand-muted">SKU: {{ $item->variant_sku ?? $item->product_sku }}</p>
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
        <h3 class="text-sm font-semibold uppercase tracking-wide">Shipping</h3>
        <div class="mt-3">
            @include('partials.order-shipping-details', ['order' => $order])
        </div>
    </div>
</div>

<div class="mt-6">
    <h3 class="text-sm font-semibold uppercase tracking-wide">Summary</h3>
    <dl class="mt-3 max-w-sm space-y-2 text-sm">
        <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ config('shop.currency_symbol') }} {{ number_format($order->subtotal, 2) }}</dd></div>
        <div class="flex justify-between gap-4">
            <dt class="shrink-0">Delivery fee</dt>
            <dd class="text-right">{{ $order->deliveryFeeLabel() }}</dd>
        </div>
        @if ($order->tax > 0)
            <div class="flex justify-between"><dt>Tax</dt><dd>{{ config('shop.currency_symbol') }} {{ number_format($order->tax, 2) }}</dd></div>
        @endif
        <div class="flex justify-between border-t border-neutral-200 pt-2 font-semibold"><dt>Total</dt><dd class="text-brand-red">{{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}</dd></div>
    </dl>
</div>

@include('admin.partials.order-invoice-actions', ['order' => $order, 'class' => 'mt-6'])

@if ($order->payment_status === \App\Enums\PaymentStatus::Paid)
    <div class="mt-6 border-t border-neutral-200 pt-6">
        <h3 class="text-sm font-semibold uppercase tracking-wide">Delivery tracking</h3>
        <div class="mt-4">
            @include('account.partials.tracking-timeline', ['steps' => $order->trackingSteps()])
        </div>

        @if ($order->isTrackable())
            <div class="mt-6">
                @include('admin.partials.order-tracking-form', ['order' => $order])
            </div>
        @endif

        <a href="{{ route('admin.orders.show', $order) }}#delivery-tracking" class="btn-outline mt-4 inline-flex w-full justify-center">
            Open full order page
        </a>
    </div>
@endif
