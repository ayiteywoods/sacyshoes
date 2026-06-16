@extends('layouts.storefront')

@section('title', $title ?? 'My Account - SACYSHOES')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="section-eyebrow">Account</p>
            <h1 class="section-title mt-1">@yield('account-heading', 'My Account')</h1>
            @hasSection('account-subheading')
                <p class="mt-2 text-sm text-brand-muted">@yield('account-subheading')</p>
            @endif
        </div>

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
