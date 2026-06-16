@extends('layouts.admin')

@section('heading', 'Website pages')
@section('subheading', 'Edit privacy policy, terms, and other footer-linked pages')

@section('content')
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($pages as $page)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $page->title }}</h2>
                        <p class="mt-1 text-sm text-brand-muted">/{{ $page->slug }}</p>
                        @if ($page->footer_group)
                            <p class="mt-1 text-xs uppercase tracking-wide text-brand-muted">
                                Footer: {{ str_replace('_', ' ', $page->footer_group) }}
                            </p>
                        @endif
                    </div>
                    <span class="text-xs font-medium {{ $page->is_active ? 'text-green-700' : 'text-brand-muted' }}">
                        {{ $page->is_active ? 'Active' : 'Hidden' }}
                    </span>
                </div>
                <a href="{{ route('admin.pages.edit', $page) }}" class="btn-outline mt-5 inline-block px-4 py-2 text-sm">
                    Edit page
                </a>
            </div>
        @endforeach
    </div>
@endsection
