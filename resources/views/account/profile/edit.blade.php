@extends('account.layout')

@section('title', 'Profile Settings - SACYSHOES')
@section('account-heading', 'Profile Settings')
@section('account-subheading', 'Update your account details and password.')

@section('account-content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="card p-6">
            <h2 class="page-heading">Personal Information</h2>

            <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="block text-sm font-medium">First name</label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="input-field">
                        @error('first_name')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium">Last name</label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="input-field">
                        @error('last_name')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="input-field">
                    @error('email')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium">Phone number</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="input-field">
                    @error('phone')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary px-6 py-2.5">
                    Save Changes
                </button>
            </form>
        </div>

        <div class="card p-6">
            <h2 class="page-heading">Change Password</h2>

            <form method="POST" action="{{ route('account.profile.password') }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium">Current password</label>
                    <input id="current_password" type="password" name="current_password" required class="input-field">
                    @error('current_password')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium">New password</label>
                    <input id="password" type="password" name="password" required class="input-field">
                    @error('password')<p class="mt-1 text-sm text-brand-red">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="input-field">
                </div>

                <button type="submit" class="btn-outline px-6 py-2.5">
                    Update Password
                </button>
            </form>
        </div>
    </div>
@endsection
