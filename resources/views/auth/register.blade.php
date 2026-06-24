@extends('layouts.guest')

@section('title', 'Register - SACYSHOES')

@section('content')
    <h1 class="page-heading">Create account</h1>
    <p class="mt-1 text-sm text-brand-muted">Shop shoes and track your orders.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-form-label for="first_name" :required="true">First name</x-form-label>
                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required class="input-field">
                @error('first_name')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-form-label for="last_name" :required="true">Last name</x-form-label>
                <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required class="input-field">
                @error('last_name')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <x-form-label for="email" :required="true">Email</x-form-label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input-field">
            @error('email')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-form-label for="phone" :required="true">Phone number</x-form-label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required class="input-field">
            @error('phone')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-form-label for="password" :required="true">Password</x-form-label>
            <input id="password" type="password" name="password" required class="input-field">
            @error('password')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-form-label for="password_confirmation" :required="true">Confirm password</x-form-label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="input-field">
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">
            Create Account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-brand-muted">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-brand-red hover:underline">Sign in</a>
    </p>
@endsection
