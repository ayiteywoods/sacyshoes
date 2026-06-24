@php
    $replacements = \App\Support\EmailReplacements::forOrder($order);
@endphp

<x-mail::message>
{!! app(\App\Services\EmailTemplateService::class)->renderBodyHtml(\App\Models\EmailTemplate::SLUG_ORDER_CANCELLED, $replacements) !!}

<x-mail::button :url="route('shop.index')">
Continue Shopping
</x-mail::button>
</x-mail::message>
