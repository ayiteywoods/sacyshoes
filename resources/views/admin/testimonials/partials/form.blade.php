@php($testimonial = $testimonial ?? null)

<div>
    <x-form-label :required="true">Quote</x-form-label>
    <textarea name="quote" rows="4" required class="input-field">{{ old('quote', $testimonial?->quote) }}</textarea>
    @error('quote')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <x-form-label :required="true">Customer name</x-form-label>
    <input type="text" name="author_name" value="{{ old('author_name', $testimonial?->author_name) }}" required class="input-field">
    @error('author_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium">Rating</label>
        <select name="rating" class="input-field">
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected((int) old('rating', $testimonial?->rating ?? 5) === $i)>{{ $i }} stars</option>
            @endfor
        </select>
        @error('rating')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Sort order</label>
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $testimonial?->sort_order ?? 0) }}" class="input-field">
        @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="flex items-center gap-2">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" id="is_active" class="h-4 w-4 rounded border-neutral-300 text-brand-red" @checked(old('is_active', $testimonial?->is_active ?? true))>
    <label for="is_active" class="text-sm">Active</label>
</div>
