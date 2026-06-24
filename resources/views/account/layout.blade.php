@extends('layouts.storefront')

@section('title', $title ?? 'My Account - SACYSHOES')

@section('content')
    @php
        $user = auth()->user();

        $accountHeroIcon = match (true) {
            request()->routeIs('account.orders.*') => 'orders',
            request()->routeIs('account.favorites.*') => 'heart',
            request()->routeIs('account.profile.*') => 'user',
            default => 'user',
        };

        if (! isset($accountHeroStats)) {
            $accountHeroStats = match (true) {
                request()->routeIs('account.dashboard') => [
                    ['value' => (string) ($stats['total'] ?? $user->orders()->count()), 'label' => 'Orders', 'icon' => 'bag', 'tone' => 'red'],
                    ['value' => (string) ($stats['pending'] ?? 0), 'label' => 'In progress', 'icon' => 'orders', 'tone' => 'white'],
                    ['value' => (string) $user->favoriteProducts()->count(), 'label' => 'Saved', 'icon' => 'heart', 'tone' => 'red'],
                ],
                request()->routeIs('account.orders.index') => [
                    ['value' => (string) $orders->total(), 'label' => 'Orders', 'icon' => 'orders', 'tone' => 'red'],
                    ['value' => (string) $user->orders()->where('payment_status', 'paid')->count(), 'label' => 'Paid', 'icon' => 'shield', 'tone' => 'white'],
                    ['value' => (string) $user->favoriteProducts()->count(), 'label' => 'Saved', 'icon' => 'heart', 'tone' => 'red'],
                ],
                request()->routeIs('account.favorites.index') => [
                    ['value' => (string) $products->count(), 'label' => 'Saved', 'icon' => 'heart', 'tone' => 'red'],
                    ['value' => (string) $user->orders()->count(), 'label' => 'Orders', 'icon' => 'bag', 'tone' => 'white'],
                    ['value' => $user->created_at->format('Y'), 'label' => 'Member', 'icon' => 'user', 'tone' => 'red'],
                ],
                request()->routeIs('account.profile.*') => [
                    ['value' => $user->created_at->format('Y'), 'label' => 'Member', 'icon' => 'user', 'tone' => 'red'],
                    ['value' => (string) $user->orders()->count(), 'label' => 'Orders', 'icon' => 'bag', 'tone' => 'white'],
                    ['value' => 'Secure', 'label' => 'Account', 'icon' => 'shield', 'tone' => 'red'],
                ],
                default => [],
            };
        }
    @endphp

    <x-page-hero
        eyebrow="Account"
        :title="trim($__env->yieldContent('account-heading'))"
        :description="trim($__env->yieldContent('account-subheading')) ?: null"
        :icon="$accountHeroIcon"
        :stats="$accountHeroStats"
    />

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="account-layout">
            <aside class="account-sidebar">
                @include('account.partials.nav')
            </aside>

            <div class="account-main">
                @yield('account-content')
            </div>
        </div>
    </div>
@endsection
