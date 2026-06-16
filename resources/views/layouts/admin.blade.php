<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - Sacy Shoes')</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/brand/logo1.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen bg-brand-light text-brand-black antialiased"
    :class="{ 'overflow-hidden': mobileSidebarOpen }"
    x-data="{
        userOpen: false,
        notificationsOpen: false,
        sidebarCollapsed: localStorage.getItem('adminSidebarCollapsed') === 'true',
        mobileSidebarOpen: false,
        adminSearchOpen: {{ request()->routeIs('admin.search') && request()->filled('q') ? 'true' : 'false' }},
        detailDrawerOpen: false,
        detailLoading: false,
        detailTitle: '',
        detailHtml: '',
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('adminSidebarCollapsed', this.sidebarCollapsed ? 'true' : 'false');
        },
        async openDetail(url) {
            this.detailDrawerOpen = true;
            this.detailLoading = true;
            this.detailTitle = '';
            this.detailHtml = '';

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to load details.');
                }

                const data = await response.json();
                this.detailTitle = data.title;
                this.detailHtml = data.html;
            } catch (error) {
                this.detailTitle = 'Unable to load';
                this.detailHtml = '<p class=\'text-sm text-brand-red\'>Could not load details. Please try again.</p>';
            } finally {
                this.detailLoading = false;
            }
        },
        printDetail() {
            window.print();
        },
    }"
    @keydown.escape.window="userOpen = false; notificationsOpen = false; detailDrawerOpen = false; mobileSidebarOpen = false; adminSearchOpen = false"
>
    <div
        x-show="mobileSidebarOpen"
        x-cloak
        class="admin-sidebar-backdrop"
        @click="mobileSidebarOpen = false"
    ></div>

    <x-admin-sidebar />

    <div class="admin-main" :class="sidebarCollapsed ? 'admin-main-collapsed' : ''">
            <header class="border-b border-neutral-200 bg-brand-white px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between gap-3 lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,56rem)_auto] lg:items-center lg:gap-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            @click="mobileSidebarOpen = true"
                            class="admin-header-menu-toggle inline-flex shrink-0 lg:hidden"
                            aria-label="Open menu"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                            </svg>
                        </button>

                        <button
                            type="button"
                            @click="toggleSidebar()"
                            class="admin-header-menu-toggle hidden shrink-0 lg:inline-flex"
                            aria-label="Toggle sidebar"
                            :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                            </svg>
                        </button>

                        <div class="min-w-0">
                            <h1 class="truncate text-base font-semibold uppercase tracking-wide sm:text-lg">@yield('heading', 'Dashboard')</h1>
                            @hasSection('subheading')
                                <p class="truncate text-xs text-brand-muted sm:text-sm">@yield('subheading')</p>
                            @endif
                        </div>
                    </div>

                    <div class="admin-navbar-search-wrap hidden min-w-0 lg:block lg:justify-self-center">
                        <x-admin-navbar-search />
                    </div>

                    <div class="flex shrink-0 items-center gap-2 justify-self-end">
                        <button
                            type="button"
                            @click="adminSearchOpen = !adminSearchOpen; if (adminSearchOpen) $nextTick(() => document.getElementById('admin-navbar-search-mobile')?.focus())"
                            class="admin-header-menu-toggle inline-flex lg:hidden"
                            aria-label="Toggle search"
                            :aria-expanded="adminSearchOpen"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </button>
                        <x-admin-notifications />
                        <x-admin-user-menu />
                    </div>
                </div>

                <div
                    x-show="adminSearchOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-3 lg:hidden"
                    @click.outside="adminSearchOpen = false"
                >
                    <x-admin-navbar-search input-id="admin-navbar-search-mobile" />
                </div>
            </header>

            @if (session('success'))
                <div class="px-4 pt-4 sm:px-6">
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="px-4 pt-4 sm:px-6">
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <main class="flex-1 p-4 sm:p-6">
                @yield('content')
            </main>

            <x-site-footer-meta-bar class="mt-auto" />
    </div>

    <x-admin-detail-drawer />

    <style>[x-cloak] { display: none !important; }</style>
    @stack('scripts')
</body>
</html>
