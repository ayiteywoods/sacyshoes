@extends('layouts.admin')

@section('heading', 'Edit category')

@section('content')
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 card p-6">
        @csrf
        @method('PUT')
        @include('admin.categories.partials.form', ['category' => $category])
        <button type="submit" class="btn-primary">Update category</button>
    </form>
@endsection
