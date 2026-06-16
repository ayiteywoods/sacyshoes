<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sacy Shoes')</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/brand/logo1.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-light text-brand-black antialiased">
    <div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-12">
        <div class="mb-8 flex justify-center">
            <x-logo size="auth" variant="light" />
        </div>
        <div class="card p-8 shadow-sm">
            @if (session('success'))
                <div class="mb-4 border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
