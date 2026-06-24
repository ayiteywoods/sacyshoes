<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance - {{ config('shop.store_name') }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/brand/logo1.webp') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-brand-cream text-brand-black antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-16">
        <div class="w-full max-w-lg border border-neutral-200 bg-brand-white p-8 text-center sm:p-10">
            <x-logo href="#" size="header" variant="light" class="pointer-events-none justify-center" />

            <p class="mt-8 text-xs font-semibold uppercase tracking-[0.25em] text-brand-red">Maintenance mode</p>
            <h1 class="mt-3 text-2xl font-semibold tracking-wide">We&rsquo;ll be back soon</h1>
            <p class="mt-4 text-sm leading-relaxed text-brand-muted">{{ $message }}</p>

            @if (config('shop.contact_email'))
                <p class="mt-6 text-sm text-brand-muted">
                    Need help? Contact us at
                    <a href="mailto:{{ config('shop.contact_email') }}" class="text-brand-red transition hover:underline">
                        {{ config('shop.contact_email') }}
                    </a>
                </p>
            @endif
        </div>
    </div>
</body>
</html>
