@extends('layouts.admin')

@section('heading', 'Add admin user')

@section('content')
    <form method="POST" action="{{ route('admin.users.store') }}" class="card w-full min-w-0 max-w-2xl space-y-4 p-4 sm:p-6">
        @csrf
        @include('admin.users.partials.form')
        <button type="submit" class="btn-primary">Create admin user</button>
    </form>
@endsection
