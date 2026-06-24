@extends('layouts.guest')

@section('title', 'Login - SACYSHOES')

@section('content')
    <h1 class="page-heading">Sign in</h1>
    <p class="mt-1 text-sm text-brand-muted">Access your account and track your order progress.</p>

    <x-order-tracking-notice compact class="mt-5" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-form-label for="email" :required="true">Email</x-form-label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input-field">
            @error('email')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-form-label for="password" :required="true">Password</x-form-label>
            <input id="password" type="password" name="password" required class="input-field">
            @error('password')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="rounded border-neutral-300 text-brand-red focus:ring-brand-red">
            Remember me
        </label>

        <div class="text-right">
            <a href="{{ route('password.request') }}" class="text-sm text-brand-red hover:underline">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">
            Sign In
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-brand-muted">
        No account?
        <a href="{{ route('register') }}" class="font-medium text-brand-red hover:underline">Create one</a>
    </p>
@endsection
