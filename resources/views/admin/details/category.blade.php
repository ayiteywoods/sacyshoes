<dl class="grid gap-4 sm:grid-cols-2">
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Name</dt>
        <dd class="mt-1 font-medium">{{ $category->name }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Slug</dt>
        <dd class="mt-1">{{ $category->slug }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Type</dt>
        <dd class="mt-1">
            @if ($category->parent)
                Subcategory under {{ $category->parent->name }}
            @else
                Main category
            @endif
        </dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Status</dt>
        <dd class="mt-1">{{ $category->status->label() }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Navbar</dt>
        <dd class="mt-1">
            @if ($category->show_in_navbar)
                Visible (sort {{ $category->navbar_sort_order }})
            @else
                Hidden
            @endif
        </dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Products</dt>
        <dd class="mt-1">{{ $category->products_count }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Created</dt>
        <dd class="mt-1">{{ $category->created_at->format('M j, Y g:i A') }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Updated</dt>
        <dd class="mt-1">{{ $category->updated_at->format('M j, Y g:i A') }}</dd>
    </div>
</dl>

@if ($category->description)
    <div class="mt-6">
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Description</dt>
        <dd class="mt-2 text-sm leading-relaxed">{{ $category->description }}</dd>
    </div>
@endif

@if (! $category->parent && $category->children_count > 0)
    <div class="mt-6">
        <p class="text-xs font-medium uppercase tracking-wide text-brand-muted">Subcategories</p>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($category->children as $child)
                <li>{{ $child->name }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php $imageUrl = $category->storefrontImageUrl(); @endphp
@if ($imageUrl)
    <div class="mt-6">
        <p class="text-xs font-medium uppercase tracking-wide text-brand-muted">Image</p>
        <img src="{{ $imageUrl }}" alt="{{ $category->name }}" class="mt-3 h-40 w-40 border border-neutral-200 object-cover">
    </div>
@endif
