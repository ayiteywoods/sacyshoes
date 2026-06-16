<x-mail::message>
# Welcome to SACYSHOES

Hi {{ $user->first_name ?? $user->name }},

Thanks for creating your account. You can now shop our latest footwear, track orders, and manage your profile anytime.

<x-mail::button :url="route('shop.index')">
Start Shopping
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
