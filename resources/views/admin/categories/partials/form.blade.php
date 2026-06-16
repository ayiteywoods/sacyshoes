@php($category = $category ?? null)

<div>
    <label class="block text-sm font-medium">Name</label>
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
    <label class="block text-sm font-medium">Status</label>
    <select name="status" class="input-field">
        @foreach (\App\Enums\CategoryStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected(old('status', $category?->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium">Image</label>
    <input type="file" name="image" accept="image/*" class="mt-1 w-full text-sm">
    @if ($category?->image)
        <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}" class="mt-3 h-24 w-24 rounded-lg object-cover">
    @endif
    @error('image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
