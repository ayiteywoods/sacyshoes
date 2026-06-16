<x-mail::message>
# Payment Confirmed

Hi {{ $order->billing_full_name }},

Your payment for order **{{ $order->order_number }}** was successful.

**Amount paid:** {{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}

We are now preparing your order.

<x-mail::button :url="route('account.orders.show', $order)">
Track Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
