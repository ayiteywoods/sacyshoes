@extends('layouts.admin')

@section('heading', 'Add shipping region')
@section('subheading', 'Set a region and its delivery options')

@section('content')
    <form method="POST" action="{{ route('admin.shipping-regions.store') }}" class="card w-full min-w-0 max-w-4xl space-y-4 p-4 sm:p-6">
        @csrf
        @include('admin.shipping-regions.partials.form', ['region' => null])
        <button type="submit" class="btn-primary">Save region</button>
    </form>
@endsection

