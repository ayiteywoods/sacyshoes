@extends('layouts.admin')

@section('heading', 'Add product')

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-4 card p-6">
        @csrf
        @include('admin.products.partials.form', ['categories' => $categories])
        <button type="submit" class="btn-primary">Save product</button>
    </form>
@endsection
