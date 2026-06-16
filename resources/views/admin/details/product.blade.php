<dl class="grid gap-4 sm:grid-cols-2">
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Name</dt>
        <dd class="mt-1 font-medium">{{ $product->name }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">SKU</dt>
        <dd class="mt-1">{{ $product->sku }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Category</dt>
        <dd class="mt-1">{{ $product->category->name }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Status</dt>
        <dd class="mt-1">{{ $product->status->label() }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Price</dt>
        <dd class="mt-1">{{ config('shop.currency_symbol') }} {{ number_format($product->price, 2) }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Selling price</dt>
        <dd class="mt-1 font-medium text-brand-red">{{ config('shop.currency_symbol') }} {{ number_format($product->sellingPrice(), 2) }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Stock</dt>
        <dd class="mt-1">
            {{ $product->quantity }}
            @if ($product->isLowStock())
                <span class="text-brand-red">(Low stock)</span>
            @endif
        </dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Created</dt>
        <dd class="mt-1">{{ $product->created_at->format('M j, Y g:i A') }}</dd>
    </div>
</dl>

@if ($product->description)
    <div class="mt-6">
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Description</dt>
        <dd class="mt-2 text-sm leading-relaxed text-brand-black">{{ $product->description }}</dd>
    </div>
@endif

@if ($product->images->isNotEmpty())
    <div class="mt-6">
        <p class="text-xs font-medium uppercase tracking-wide text-brand-muted">Images</p>
        <div class="mt-3 flex flex-wrap gap-3">
            @foreach ($product->images as $image)
                <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $product->name }}" class="h-24 w-24 border border-neutral-200 object-cover">
            @endforeach
        </div>
    </div>
@endif
