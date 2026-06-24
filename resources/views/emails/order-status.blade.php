@php
    $replacements = \App\Support\EmailReplacements::forOrderStatus($order);
@endphp

<x-mail::message>
{!! app(\App\Services\EmailTemplateService::class)->renderBodyHtml(\App\Models\EmailTemplate::SLUG_ORDER_STATUS, $replacements) !!}

@include('emails.partials.order-summary', ['order' => $order])

<x-mail::button :url="\App\Support\OrderMailUrls::viewOrder($order)">
View Order Tracking
</x-mail::button>
</x-mail::message>
