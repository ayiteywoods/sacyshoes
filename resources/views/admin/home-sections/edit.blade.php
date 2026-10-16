@extends('layouts.admin')

@section('heading', 'Edit '.$section->name)

@section('content')
    <form method="POST" action="{{ route('admin.home-sections.update', $section) }}" enctype="multipart/form-data" class="card max-w-3xl space-y-4 p-6">
        @csrf
        @method('PUT')

        @if (in_array($section->key, [\App\Models\HomeSection::KEY_HERO, \App\Models\HomeSection::KEY_CTA, \App\Models\HomeSection::KEY_SHOP_CATEGORY, \App\Models\HomeSection::KEY_NEW_ARRIVALS, \App\Models\HomeSection::KEY_TESTIMONIALS_HEADER]))
            <div>
                <label class="block text-sm font-medium">Eyebrow</label>
                <input type="text" name="eyebrow" value="{{ old('eyebrow', $section->eyebrow) }}" class="input-field">
            </div>
        @endif

        @if (in_array($section->key, [\App\Models\HomeSection::KEY_HERO, \App\Models\HomeSection::KEY_CTA, \App\Models\HomeSection::KEY_SHOP_CATEGORY, \App\Models\HomeSection::KEY_NEW_ARRIVALS, \App\Models\HomeSection::KEY_TESTIMONIALS_HEADER, \App\Models\HomeSection::KEY_DELIVERY_NOTICE]))
            <div>
                <label class="block text-sm font-medium">Title</label>
                <input type="text" name="title" value="{{ old('title', $section->title) }}" class="input-field">
            </div>
        @endif

        @if ($section->key === \App\Models\HomeSection::KEY_HERO)
            <div>
                <label class="block text-sm font-medium">Title highlight (red text)</label>
                <input type="text" name="title_highlight" value="{{ old('title_highlight', $section->title_highlight) }}" class="input-field">
            </div>
        @endif

        @if (in_array($section->key, [\App\Models\HomeSection::KEY_HERO, \App\Models\HomeSection::KEY_CTA, \App\Models\HomeSection::KEY_SHOP_CATEGORY, \App\Models\HomeSection::KEY_NEW_ARRIVALS, \App\Models\HomeSection::KEY_FREE_DELIVERY, \App\Models\HomeSection::KEY_DELIVERY_NOTICE]))
            <div>
                <label class="block text-sm font-medium">Body / description</label>
                <textarea name="body" rows="4" class="input-field">{{ old('body', $section->body) }}</textarea>
                @if ($section->key === \App\Models\HomeSection::KEY_FREE_DELIVERY)
                    <p class="mt-1 text-xs text-brand-muted">Use <code>{currency_symbol}</code> and <code>{threshold}</code> for dynamic values.</p>
                @endif
            </div>
        @endif

        @if (in_array($section->key, [\App\Models\HomeSection::KEY_HERO, \App\Models\HomeSection::KEY_CTA, \App\Models\HomeSection::KEY_SHOP_CATEGORY, \App\Models\HomeSection::KEY_NEW_ARRIVALS]))
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium">Primary button label</label>
                    <input type="text" name="primary_label" value="{{ old('primary_label', $section->primary_label) }}" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium">Primary button URL</label>
                    <input type="text" name="primary_url" value="{{ old('primary_url', $section->primary_url) }}" class="input-field" placeholder="/shop">
                </div>
            </div>
        @endif

        @if (in_array($section->key, [\App\Models\HomeSection::KEY_HERO, \App\Models\HomeSection::KEY_CTA]))
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium">Secondary button label</label>
                    <input type="text" name="secondary_label" value="{{ old('secondary_label', $section->secondary_label) }}" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium">Secondary button URL</label>
                    <input type="text" name="secondary_url" value="{{ old('secondary_url', $section->secondary_url) }}" class="input-field" placeholder="/shop">
                </div>
            </div>
        @endif

        @if ($section->key === \App\Models\HomeSection::KEY_HERO)
            <div>
                <label class="block text-sm font-medium">Hero image</label>
                <input type="file" name="image" accept="image/*" class="mt-1 w-full text-sm">
                @if ($section->imageUrl())
                    <img src="{{ $section->imageUrl() }}" alt="Hero" class="mt-3 h-40 w-full max-w-md object-cover">
                @endif
                <p class="mt-1 text-xs text-brand-muted">Large images are automatically compressed to {{ \App\Support\ImageUpload::targetLabel(4096) }}.</p>
                @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endif

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="is_active" class="h-4 w-4 rounded border-neutral-300 text-brand-red" @checked(old('is_active', $section->is_active))>
            <label for="is_active" class="text-sm">Show this section on the website</label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Save section</button>
            <a href="{{ route('admin.home-sections.index') }}" class="btn-outline px-4 py-2">Cancel</a>
        </div>
    </form>
@endsection
