@extends('layouts.admin')

@section('heading', 'Edit '.$page->title)

@section('content')
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="card max-w-3xl space-y-4 p-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium">Page title</label>
            <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="input-field">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Content</label>
            <textarea name="body" rows="18" class="input-field font-mono text-sm">{{ old('body', $page->body) }}</textarea>
            <p class="mt-1 text-xs text-brand-muted">Markdown is supported (headings, lists, links). Keep it simple for best results.</p>
            @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active" class="h-4 w-4 rounded border-neutral-300 text-brand-red" @checked(old('is_active', $page->is_active))>
            <label for="is_active" class="text-sm">Publish this page on the website</label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Save page</button>
            <a href="{{ route('admin.pages.index') }}" class="btn-outline px-4 py-2">Cancel</a>
            @if ($page->is_active)
                <a href="{{ route('pages.show', $page) }}" target="_blank" class="btn-outline px-4 py-2">Preview</a>
            @endif
        </div>
    </form>
@endsection
