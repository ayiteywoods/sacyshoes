<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Sacy Shoes'))</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/brand/logo1.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-cream text-brand-black antialiased" x-data="{ searchOpen: {{ request()->filled('q') ? 'true' : 'false' }}, userOpen: false }">
    <div class="sticky top-0 z-50">
        <x-category-ticker />

        <header class="border-b border-neutral-200 bg-brand-white/95 backdrop-blur" @keydown.escape.window="searchOpen = false; userOpen = false">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-4 gap-y-3 px-4 py-3 sm:px-6 lg:px-8">
            <x-logo href="{{ route('home') }}" size="header" variant="light" />

            {{-- Nav links — always visible as text --}}
            <nav class="flex flex-wrap items-center gap-x-4 gap-y-1 sm:gap-x-6">
                <a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*') ? 'text-brand-red' : '' }}">Shop All</a>
                <a href="{{ route('shop.index') }}" class="nav-link">New Arrivals</a>
            </nav>

            <div class="flex items-center gap-1 sm:gap-2">
                <x-currency-badge />

                <button
                    type="button"
                    @click="searchOpen = !searchOpen; userOpen = false; if (searchOpen) $nextTick(() => document.getElementById('navbar-search')?.focus())"
                    class="rounded-none p-2 text-brand-black transition hover:bg-brand-light"
                    :class="searchOpen ? 'bg-brand-light text-brand-red' : ''"
                    aria-label="Search products"
                    :aria-expanded="searchOpen"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </button>

                <a href="{{ route('cart.index') }}" class="relative rounded-none p-2 text-brand-black transition hover:bg-brand-light" title="View cart">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-none bg-brand-red text-[10px] font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>

                <x-user-menu />
            </div>
        </div>

        {{-- Expandable search bar --}}
        <div
            x-show="searchOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="border-t border-neutral-200 bg-brand-white px-4 py-4 sm:px-6 lg:px-8"
            @click.outside="searchOpen = false"
        >
            <div class="mx-auto max-w-2xl">
                <x-navbar-search />
            </div>
        </div>
    </header>
    </div>

    @if (session('success'))
        <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div class="border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div class="border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-900">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="mt-20 border-t border-neutral-800 bg-brand-black text-white">
        <x-storefront-footer-trust-bar />

        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-5">
                <div class="sm:col-span-2 lg:col-span-1">
                    <x-logo href="{{ route('home') }}" size="header" variant="dark" />
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-neutral-400">
                        Premium footwear curated for every occasion. Quality shoes delivered across Ghana.
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider">Shop</h3>
                    <ul class="mt-4 space-y-2 text-sm text-neutral-400">
                        <li><a href="{{ route('shop.index') }}" class="transition hover:text-brand-red">All Products</a></li>
                        <li><a href="{{ route('shop.index') }}" class="transition hover:text-brand-red">New Arrivals</a></li>
                        <li><a href="{{ route('shop.index') }}" class="transition hover:text-brand-red">Sale Items</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider">Customer Care</h3>
                    <ul class="mt-4 space-y-2 text-sm text-neutral-400">
                        @forelse ($footerCustomerCarePages as $footerPage)
                            <li>
                                <a href="{{ route('pages.show', $footerPage) }}" class="transition hover:text-brand-red">
                                    {{ $footerPage->title }}
                                </a>
                            </li>
                        @empty
                            <li><a href="#" class="transition hover:text-brand-red">Delivery Info</a></li>
                            <li><a href="#" class="transition hover:text-brand-red">Returns Policy</a></li>
                            <li><a href="#" class="transition hover:text-brand-red">Contact Us</a></li>
                        @endforelse
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider">Account</h3>
                    <ul class="mt-4 space-y-2 text-sm text-neutral-400">
                        @auth
                            <li><a href="{{ route('account.orders.index') }}" class="transition hover:text-brand-red">My Orders</a></li>
                            <li><a href="{{ route('account.dashboard') }}" class="transition hover:text-brand-red">My Account</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="transition hover:text-brand-red">Login</a></li>
                            <li><a href="{{ route('register') }}" class="transition hover:text-brand-red">Register</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider">Legal</h3>
                    <ul class="mt-4 space-y-2 text-sm text-neutral-400">
                        @forelse ($footerLegalPages as $footerPage)
                            <li>
                                <a href="{{ route('pages.show', $footerPage) }}" class="transition hover:text-brand-red">
                                    {{ $footerPage->title }}
                                </a>
                            </li>
                        @empty
                            <li><a href="#" class="transition hover:text-brand-red">Privacy Policy</a></li>
                            <li><a href="#" class="transition hover:text-brand-red">Terms &amp; Conditions</a></li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-neutral-800 pt-8">
                <x-site-footer-meta-bar embedded />
            </div>
        </div>
    </footer>

    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
