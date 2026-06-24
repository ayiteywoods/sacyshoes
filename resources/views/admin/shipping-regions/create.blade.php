@extends('layouts.admin')

@section('heading', 'Add shipping region')
@section('subheading', 'Set a region and its delivery options')

@section('content')
    <form method="POST" action="{{ route('admin.shipping-regions.store') }}" class="max-w-4xl space-y-4 card p-6">
        @csrf
        @include('admin.shipping-regions.partials.form', ['region' => null])
        <button type="submit" class="btn-primary">Save region</button>
    </form>
@endsection

