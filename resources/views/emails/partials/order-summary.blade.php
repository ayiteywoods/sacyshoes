@props(['order', 'showEmail' => false])

**Order #{{ $order->order_number }}** ({{ ($order->paid_at ?? $order->created_at)->format('F j, Y') }})

@foreach ($order->items as $item)
- {{ $item->product_name }}@if ($item->optionLabel()) ({{ $item->optionLabel() }})@endif — {{ $item->quantity }} × {{ config('shop.currency_symbol') }}{{ number_format($item->unit_price, 2) }} = {{ config('shop.currency_symbol') }}{{ number_format($item->total_price, 2) }}
@endforeach

**Subtotal:** {{ config('shop.currency_symbol') }}{{ number_format($order->subtotal, 2) }}

@if ((float) $order->shipping_fee > 0)
**Delivery ({{ $order->shipping_option_name }}):** {{ config('shop.currency_symbol') }}{{ number_format($order->shipping_fee, 2) }}
@elseif ($order->shipping_region_name)
**Delivery:** {{ $order->shipping_region_name }} — pay rider on delivery
@endif

@if ((float) $order->tax > 0)
**Tax:** {{ config('shop.currency_symbol') }}{{ number_format($order->tax, 2) }}
@endif

**Total:** {{ config('shop.currency_symbol') }}{{ number_format($order->total, 2) }}

**Ship to:**  
{{ $order->shipping_full_name }}  
{{ $order->shipping_address }}, {{ $order->shipping_city }}  
{{ $order->shipping_country }}  
{{ $order->shipping_phone }}@if ($showEmail && $order->customerEmail())

{{ $order->customerEmail() }}@endif
