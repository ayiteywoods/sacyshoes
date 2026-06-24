@php($category = $category ?? null)
@php($isSubcategory = filled(old('parent_id', $category?->parent_id)))

<div>
    <label class="block text-sm font-medium">Parent category</label>
    <select name="parent_id" class="input-field" id="category-parent-id">
        <option value="">Main category</option>
        @foreach ($parentCategories as $parentCategory)
            <option
                value="{{ $parentCategory->id }}"
                @selected((string) old('parent_id', $category?->parent_id) === (string) $parentCategory->id)
            >
                {{ $parentCategory->name }}
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-brand-muted">Choose a main category to create a subcategory, or leave blank for a top-level category.</p>
    @error('parent_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <x-form-label :required="true">Name</x-form-label>
    <input type="text" name="name" value="{{ old('name', $category?->name) }}" required class="input-field">
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $category?->slug) }}" class="input-field">
    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium">Description</label>
    <textarea name="description" rows="4" class="input-field">{{ old('description', $category?->description) }}</textarea>
    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <x-form-label :required="true">Status</x-form-label>
    <select name="status" class="input-field">
        @foreach (\App\Enums\CategoryStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(old('status', $category?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid gap-4 sm:grid-cols-2" id="category-navbar-fields" @if($isSubcategory) hidden @endif>
    <div>
        <label class="inline-flex items-center gap-2 text-sm font-medium">
            <input
                type="checkbox"
                name="show_in_navbar"
                value="1"
                class="rounded border-neutral-300 text-brand-red focus:ring-brand-red"
                @checked(old('show_in_navbar', $category?->show_in_navbar))
            >
            Show in storefront navbar
        </label>
        <p class="mt-1 text-xs text-brand-muted">Appears in the header links and scrolling category bar.</p>
        @error('show_in_navbar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Navbar sort order</label>
        <input
            type="number"
            name="navbar_sort_order"
            value="{{ old('navbar_sort_order', $category?->navbar_sort_order ?? 0) }}"
            min="0"
            max="9999"
            class="input-field"
        >
        <p class="mt-1 text-xs text-brand-muted">Lower numbers appear first in the navbar.</p>
        @error('navbar_sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium">Image</label>
    <input type="file" name="image" accept="image/*" class="mt-1 w-full text-sm">
    @if ($category?->image)
        <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}" class="mt-3 h-24 w-24 rounded-lg object-cover">
    @endif
    <p class="mt-1 text-xs text-brand-muted">JPG, PNG, WebP, GIF, AVIF, or HEIC. Large images are automatically compressed to {{ \App\Support\ImageUpload::targetLabel(5120) }}.</p>
    @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<script>
    document.getElementById('category-parent-id')?.addEventListener('change', (event) => {
        const navbarFields = document.getElementById('category-navbar-fields');
        if (!navbarFields) return;
        navbarFields.hidden = event.target.value !== '';
    });
</script>
