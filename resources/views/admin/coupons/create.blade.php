@extends('layouts.admin')

@section('heading', 'Add coupon')

@section('content')
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="card max-w-3xl space-y-4 p-6">
        @csrf
        @include('admin.coupons.partials.form')
        <button type="submit" class="btn-primary">Save coupon</button>
    </form>
@endsection
