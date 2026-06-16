@extends('layouts.guest')

@section('title', 'Reset Password - SACYSHOES')

@section('content')
    <h1 class="page-heading">Reset password</h1>
    <p class="mt-1 text-sm text-brand-muted">Choose a new password for your account.</p>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-medium">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="input-field">
            @error('email')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium">New password</label>
            <input id="password" type="password" name="password" required class="input-field">
            @error('password')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="input-field">
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">
            Reset Password
        </button>
    </form>
@endsection
