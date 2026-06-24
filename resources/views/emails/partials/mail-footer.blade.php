@php
    $settings = \App\Models\StoreSetting::current();
    $socialLinks = $settings->socialLinks();
    $storeName = \App\Support\MailBranding::storeName();
    $contactEmail = config('shop.contact_email');
    $contactPhone = config('shop.contact_phone');
@endphp

© {{ date('Y') }} {{ $storeName }}. {{ __('All rights reserved.') }}

@if ($contactEmail || $contactPhone)
@if ($contactEmail && $contactPhone)
Questions? [{{ $contactEmail }}](mailto:{{ $contactEmail }}) or {{ $contactPhone }}.
@elseif ($contactEmail)
Questions? [{{ $contactEmail }}](mailto:{{ $contactEmail }}).
@else
Questions? Call {{ $contactPhone }}.
@endif
@endif

@if (count($socialLinks))
@foreach ($socialLinks as $link)
[{{ $link['label'] }}]({{ $link['url'] }})
@endforeach
@endif
