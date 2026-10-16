@extends('layouts.admin')

@section('heading', 'Add category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 card p-6">
        @csrf
        @include('admin.categories.partials.form')
        <button type="submit" class="btn-primary">Save category</button>
    </form>
@endsection
