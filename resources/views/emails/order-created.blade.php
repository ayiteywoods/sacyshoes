<x-mail::message>
# Order Received

Hi {{ $order->billing_full_name }},

We received your order **{{ $order->order_number }}**. Complete payment to confirm it.

**Total:** {{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}

<x-mail::button :url="route('checkout.success', $order)">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
