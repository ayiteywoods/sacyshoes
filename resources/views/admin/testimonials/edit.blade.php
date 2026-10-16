@extends('layouts.admin')

@section('heading', 'Edit testimonial')

@section('content')
    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" class="card max-w-2xl space-y-4 p-6">
        @csrf
        @method('PUT')
        @include('admin.testimonials.partials.form', ['testimonial' => $testimonial])
        <button type="submit" class="btn-primary">Save changes</button>
    </form>
@endsection
