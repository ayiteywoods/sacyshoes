@extends('layouts.admin')

@section('heading', 'Edit admin user')

@section('content')
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card max-w-2xl space-y-4 p-6">
        @csrf
        @method('PUT')
        @include('admin.users.partials.form', ['user' => $user])
        <button type="submit" class="btn-primary">Save changes</button>
    </form>
@endsection
