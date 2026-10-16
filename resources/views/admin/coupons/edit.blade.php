@extends('layouts.admin')

@section('heading', 'Edit coupon')

@section('content')
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="card w-full min-w-0 max-w-3xl space-y-4 p-4 sm:p-6">
        @csrf
        @method('PUT')
        @include('admin.coupons.partials.form', ['coupon' => $coupon])
        <button type="submit" class="btn-primary">Update coupon</button>
    </form>
@endsection
