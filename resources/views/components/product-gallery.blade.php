@props(['product'])

@php
    $galleryImages = $product->images
        ->sortBy('sort_order')
        ->sortByDesc('is_primary')
        ->map(fn ($image) => [
            'url' => asset('storage/'.$image->path),
            'alt' => $product->name,
        ])
        ->values();
@endphp

<div
    class="product-gallery"
    x-data="{
        images: @js($galleryImages),
        active: 0,
        lightboxOpen: false,
        get current() {
            return this.images[this.active] ?? null;
        },
        select(index) {
            this.active = index;
        },
        openLightbox() {
            if (this.current) {
                this.lightboxOpen = true;
            }
        },
        closeLightbox() {
            this.lightboxOpen = false;
        },
        next() {
            if (this.images.length < 2) return;
            this.active = (this.active + 1) % this.images.length;
        },
        prev() {
            if (this.images.length < 2) return;
            this.active = (this.active - 1 + this.images.length) % this.images.length;
        },
    }"
    @keydown.escape.window="closeLightbox()"
    @keydown.arrow-right.window="lightboxOpen && next()"
    @keydown.arrow-left.window="lightboxOpen && prev()"
>
    @if ($galleryImages->isEmpty())
        <div class="product-gallery-main flex items-center justify-center text-brand-muted">
            <span class="absolute inset-0 flex items-center justify-center">No image available</span>
        </div>
    @else
        <div
            class="product-gallery-main"
            role="button"
            tabindex="0"
            @click="openLightbox()"
            @keydown.enter.prevent="openLightbox()"
            @keydown.space.prevent="openLightbox()"
            aria-label="View larger image"
        >
            <img
                :src="current?.url"
                :alt="current?.alt"
                class="product-gallery-main-image"
            >
            <span class="product-gallery-main-overlay">
                <span class="rounded-full bg-white/90 px-3 py-1.5 text-xs font-medium uppercase tracking-wide text-brand-black shadow">
                    Click to enlarge
                </span>
            </span>
        </div>

        @if ($galleryImages->count() > 1)
            <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                <template x-for="(image, index) in images" :key="index">
                    <button
                        type="button"
                        class="product-gallery-thumb shrink-0"
                        :class="{ 'product-gallery-thumb-active': active === index }"
                        @click="select(index)"
                        :aria-label="'Show image ' + (index + 1)"
                    >
                        <img :src="image.url" :alt="image.alt" class="product-gallery-thumb-image">
                    </button>
                </template>
            </div>
        @endif

        <div
            x-show="lightboxOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="product-gallery-lightbox"
            @click.self="closeLightbox()"
        >
            <button type="button" class="product-gallery-lightbox-close" @click="closeLightbox()" aria-label="Close">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <template x-if="images.length > 1">
                <button type="button" class="product-gallery-lightbox-nav product-gallery-lightbox-prev" @click="prev()" aria-label="Previous image">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                    </svg>
                </button>
            </template>

            <img
                :src="current?.url"
                :alt="current?.alt"
                class="max-h-[85vh] max-w-[90vw] object-contain"
            >

            <template x-if="images.length > 1">
                <button type="button" class="product-gallery-lightbox-nav product-gallery-lightbox-next" @click="next()" aria-label="Next image">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </button>
            </template>

            <p class="absolute bottom-6 left-1/2 -translate-x-1/2 text-sm text-white/80" x-show="images.length > 1" x-text="(active + 1) + ' / ' + images.length"></p>
        </div>
    @endif
</div>
