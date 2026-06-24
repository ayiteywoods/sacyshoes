<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="{{ \App\Support\MailBranding::logoUrl() }}" class="logo" alt="{{ \App\Support\MailBranding::storeName() }}" width="140">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
@include('emails.partials.mail-footer')
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
