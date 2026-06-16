@extends('layouts.admin')

@section('heading', 'Edit product')

@section('content')
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="max-w-3xl space-y-4 card p-6">
        @csrf
        @method('PUT')
        @include('admin.products.partials.form', ['categories' => $categories, 'product' => $product])
        <button type="submit" class="btn-primary">Update product</button>
    </form>
@endsection
