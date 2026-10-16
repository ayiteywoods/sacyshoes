@extends('layouts.admin')

@section('heading', 'Edit product')

@section('content')
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="card w-full min-w-0 max-w-3xl space-y-4 p-4 sm:p-6">
        @csrf
        @include('admin.products.partials.form', ['categoryTree' => $categoryTree, 'product' => $product])
        <button type="submit" class="btn-primary">Update product</button>
    </form>
@endsection
