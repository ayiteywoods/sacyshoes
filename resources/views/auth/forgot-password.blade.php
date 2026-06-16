@extends('layouts.guest')

@section('title', 'Forgot Password - SACYSHOES')

@section('content')
    <h1 class="page-heading">Forgot password</h1>
    <p class="mt-1 text-sm text-brand-muted">We will email you a reset link.</p>

    @if (session('success'))
        <div class="mt-4 border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input-field">
            @error('email')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">
            Send Reset Link
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-brand-muted">
        <a href="{{ route('login') }}" class="font-medium text-brand-red hover:underline">Back to sign in</a>
    </p>
@endsection
