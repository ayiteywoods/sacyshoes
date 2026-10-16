@extends('layouts.admin')

@section('heading', 'Edit coupon')

@section('content')
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="card max-w-3xl space-y-4 p-6">
        @csrf
        @method('PUT')
        @include('admin.coupons.partials.form', ['coupon' => $coupon])
        <button type="submit" class="btn-primary">Update coupon</button>
    </form>
@endsection
