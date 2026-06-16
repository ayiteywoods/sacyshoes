<x-mail::message>
# Order Update

Hi {{ $order->billing_full_name }},

Your order **{{ $order->order_number }}** is now **{{ $order->status->label() }}**.

@if ($order->status->value === 'shipped')
Your package is on its way.
@elseif ($order->status->value === 'delivered')
We hope you enjoy your purchase.
@endif

<x-mail::button :url="route('account.orders.show', $order)">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
