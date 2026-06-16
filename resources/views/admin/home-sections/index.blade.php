@extends('layouts.admin')

@section('heading', 'Homepage sections')
@section('subheading', 'Edit hero, banners, call to action, and other storefront content')

@section('content')
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($sections as $section)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $section->name }}</h2>
                        <p class="mt-1 text-sm text-brand-muted">{{ $section->title ?: $section->eyebrow ?: 'Content section' }}</p>
                    </div>
                    <span class="text-xs font-medium {{ $section->is_active ? 'text-green-700' : 'text-brand-muted' }}">
                        {{ $section->is_active ? 'Active' : 'Hidden' }}
                    </span>
                </div>
                <a href="{{ route('admin.home-sections.edit', $section) }}" class="btn-outline mt-5 inline-block px-4 py-2 text-sm">
                    Edit section
                </a>
            </div>
        @endforeach
    </div>
@endsection
