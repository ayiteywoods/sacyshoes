@extends('layouts.admin')

@section('heading', 'Edit shipping region')
@section('subheading', 'Update delivery options and prices')

@section('content')
    <form method="POST" action="{{ route('admin.shipping-regions.update', $region) }}" class="max-w-4xl space-y-4 card p-6">
        @csrf
        @method('PUT')
        @include('admin.shipping-regions.partials.form', ['region' => $region])
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn-primary">Save changes</button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.shipping-regions.destroy', $region) }}" class="mt-3 max-w-4xl">
        @csrf
        @method('DELETE')
        <button
            type="button"
            class="btn-outline-red px-5 py-2.5"
            @click="adminConfirm('Delete this region?', $el.closest('form'))"
        >
            Delete
        </button>
    </form>
@endsection

