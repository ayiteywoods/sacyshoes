@php
    $replacements = \App\Support\EmailReplacements::forOrder($order);
@endphp

<x-mail::message>
{!! app(\App\Services\EmailTemplateService::class)->renderBodyHtml(\App\Models\EmailTemplate::SLUG_PAYMENT_RECEIVED, $replacements) !!}

@include('emails.partials.order-summary', ['order' => $order])

<x-mail::button :url="\App\Support\OrderMailUrls::invoice($order)">
View Invoice
</x-mail::button>

<x-mail::button :url="\App\Support\OrderMailUrls::viewOrder($order)">
View Order
</x-mail::button>
</x-mail::message>
