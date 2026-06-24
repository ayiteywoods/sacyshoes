@php
    $replacements = \App\Support\EmailReplacements::forUser($user);
@endphp

<x-mail::message>
{!! app(\App\Services\EmailTemplateService::class)->renderBodyHtml(\App\Models\EmailTemplate::SLUG_WELCOME, $replacements) !!}

<x-mail::button :url="route('shop.index')">
Start Shopping
</x-mail::button>
</x-mail::message>
