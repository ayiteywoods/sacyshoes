@extends('layouts.admin')

@section('heading', 'Add admin user')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" class="card max-w-2xl space-y-4 p-6">
        @csrf
        @include('admin.users.partials.form')
        <button type="submit" class="btn-primary">Create admin user</button>
    </form>
@endsection
