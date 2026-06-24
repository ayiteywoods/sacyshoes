<dl class="space-y-3 text-sm">
    <div>
        <dt class="text-brand-muted">Name</dt>
        <dd class="font-medium">{{ $order->shipping_full_name }}</dd>
    </div>
    <div>
        <dt class="text-brand-muted">Phone</dt>
        <dd class="font-medium">{{ $order->shipping_phone }}</dd>
    </div>
    @if ($order->shipping_email)
        <div>
            <dt class="text-brand-muted">Email</dt>
            <dd class="font-medium">{{ $order->shipping_email }}</dd>
        </div>
    @endif
    <div>
        <dt class="text-brand-muted">Address</dt>
        <dd class="font-medium">
            {{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_country }}
        </dd>
    </div>
    @if ($order->shipping_region_name)
        <div>
            <dt class="text-brand-muted">Region</dt>
            <dd class="font-medium">{{ $order->shipping_region_name }}</dd>
        </div>
    @endif
    @if ($order->shipping_option_name)
        <div>
            <dt class="text-brand-muted">Delivery option</dt>
            <dd class="font-medium">{{ $order->shipping_option_name }}</dd>
        </div>
    @endif
    @if ($order->customer_comment)
        <div>
            <dt class="text-brand-muted">Add Note</dt>
            <dd class="font-medium whitespace-pre-line">{{ $order->customer_comment }}</dd>
        </div>
    @endif
    <div>
        <dt class="text-brand-muted">Delivery fee</dt>
        <dd class="font-medium">{{ $order->deliveryFeeLabel() }}</dd>
    </div>
</dl>
