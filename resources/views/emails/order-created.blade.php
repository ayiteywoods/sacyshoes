@php
    $replacements = \App\Support\EmailReplacements::forOrder($order);
@endphp

<x-mail::message>
{!! app(\App\Services\EmailTemplateService::class)->renderBodyHtml(\App\Models\EmailTemplate::SLUG_ORDER_CREATED, $replacements) !!}

@include('emails.partials.order-summary', ['order' => $order])

<x-mail::button :url="\App\Support\OrderMailUrls::payOrder($order)">
Pay Now
</x-mail::button>

<x-mail::button :url="\App\Support\OrderMailUrls::viewOrder($order)">
View Order Details
</x-mail::button>
</x-mail::message>
