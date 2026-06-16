@php($product = $product ?? null)

<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium">Name</label>
        <input type="text" name="name" value="{{ old('name', $product?->name) }}" required class="input-field">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $product?->sku) }}" required class="input-field">
        @error('sku')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Category</label>
        <select name="category_id" required class="input-field">
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Price (GHS)</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $product?->price) }}" required class="input-field">
        @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Discount price (GHS)</label>
        <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price', $product?->discount_price) }}" class="input-field">
        @error('discount_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Quantity</label>
        <input type="number" name="quantity" value="{{ old('quantity', $product?->quantity ?? 0) }}" required class="input-field">
        @error('quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Status</label>
        <select name="status" class="input-field">
            @foreach (\App\Enums\ProductStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $product?->status?->value ?? 'draft') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium">Description</label>
        <textarea name="description" rows="5" class="input-field">{{ old('description', $product?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium">Product images</label>
        <input type="file" name="images[]" accept="image/*" multiple class="mt-1 w-full text-sm">
        @if ($product?->images?->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($product->images as $image)
                    <img src="{{ asset('storage/'.$image->path) }}" alt="" class="h-20 w-20 rounded-lg object-cover">
                @endforeach
            </div>
        @endif
        @error('images')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('images.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
