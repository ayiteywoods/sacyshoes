@php
    $replacements = \App\Support\EmailReplacements::forOrder($order);
@endphp

<x-mail::message>
{!! app(\App\Services\EmailTemplateService::class)->renderBodyHtml(\App\Models\EmailTemplate::SLUG_PAYMENT_RECEIVED, $replacements) !!}

@include('emails.partials.order-summary', ['order' => $order, 'showEmail' => true])
</x-mail::message>
