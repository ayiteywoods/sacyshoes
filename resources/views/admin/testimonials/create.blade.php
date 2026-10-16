@extends('layouts.admin')

@section('heading', 'Add testimonial')

@section('content')
    <form method="POST" action="{{ route('admin.testimonials.store') }}" class="card w-full min-w-0 max-w-2xl space-y-4 p-4 sm:p-6">
        @csrf
        @include('admin.testimonials.partials.form')
        <button type="submit" class="btn-primary">Save testimonial</button>
    </form>
@endsection
