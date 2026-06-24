@php
    use App\Models\HomeSection;

    $hero = $sections->get(HomeSection::KEY_HERO);
    $shopCategory = $sections->get(HomeSection::KEY_SHOP_CATEGORY);
    $cta = $sections->get(HomeSection::KEY_CTA);
    $newArrivals = $sections->get(HomeSection::KEY_NEW_ARRIVALS);
@endphp

@extends('layouts.storefront')

@section('title', 'SACYSHOES — Premium Footwear')

@section('content')
    @if ($hero?->is_active)
        <section
            class="hero-section"
            @if ($hero->imageUrl())
                style="background-image: url('{{ $hero->imageUrl() }}');"
            @endif
        >
            <div class="hero-section-overlay absolute inset-0" aria-hidden="true"></div>

            <div class="relative z-10 mx-auto max-w-3xl">
                @if ($hero->eyebrow)
                    <p class="section-eyebrow">{{ $hero->eyebrow }}</p>
                @endif
                <h1 class="hero-display-title hero-display-title-on-image mt-4">
                    {{ $hero->title }}
                    @if ($hero->title_highlight)
                        <br>
                        <span class="text-white">{{ $hero->title_highlight }}</span>
                    @endif
                </h1>
                @if ($hero->body)
                    <p class="mx-auto mt-6 max-w-2xl text-sm leading-relaxed text-white/90 sm:text-base">{{ $hero->body }}</p>
                @endif
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    @if ($hero->primary_label)
                        <a href="{{ $hero->resolvedUrl($hero->primary_url) }}" class="btn-primary px-6 py-3">{{ $hero->primary_label }}</a>
                    @endif
                    @if ($hero->secondary_label)
                        <a href="{{ $hero->resolvedUrl($hero->secondary_url) }}" class="hero-btn-outline px-6 py-3">{{ $hero->secondary_label }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @include('storefront.partials.free-delivery-banner', ['section' => $sections->get(HomeSection::KEY_FREE_DELIVERY)])

    @if ($shopCategory?->is_active)
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    @if ($shopCategory->eyebrow)
                        <p class="section-eyebrow">{{ $shopCategory->eyebrow }}</p>
                    @endif
                    @if ($shopCategory->title)
                        <h2 class="section-title">{{ $shopCategory->title }}</h2>
                    @endif
                    @if ($shopCategory->body)
                        <p class="mt-2 text-brand-muted">{{ $shopCategory->body }}</p>
                    @endif
                </div>
                @if ($shopCategory->primary_label)
                    <a href="{{ $shopCategory->resolvedUrl($shopCategory->primary_url) }}" class="text-sm font-medium text-brand-red transition hover:underline">
                        {{ $shopCategory->primary_label }} &rarr;
                    </a>
                @endif
            </div>

            <div class="mt-10 grid grid-cols-2 gap-4">
                @forelse ($categories as $category)
                    <a
                        href="{{ route('shop.index', ['category' => $category->id]) }}"
                        class="category-card category-card-grid group"
                    >
                        @if($category->storefrontImageUrl())
                            <div
                                class="category-card-image"
                                style="background-image: url('{{ $category->storefrontImageUrl() }}');"
                            ></div>
                        @endif
                        <div class="category-card-caption">
                            <h3 class="text-xs font-normal uppercase tracking-wide sm:text-sm">{{ $category->name }}</h3>
                        </div>
                        <span class="category-card-icon" aria-hidden="true">
                            <x-nav-icon :icon="$category->storefrontIcon()" class="h-4 w-4" />
                        </span>
                    </a>
                @empty
                    <p class="w-full text-center text-brand-muted">Categories coming soon.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if ($cta?->is_active)
        <section class="shop-hero relative overflow-hidden border-y border-neutral-800 bg-brand-black py-16 text-white">
            <div class="shop-hero-glow shop-hero-glow-left" aria-hidden="true"></div>
            <div class="shop-hero-glow shop-hero-glow-right" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-start justify-between gap-8 sm:flex-row sm:items-center">
                    <div class="max-w-2xl">
                        @if ($cta->eyebrow)
                            <div class="flex items-center gap-2.5">
                                <span class="shop-hero-icon">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        @include('components.partials.hero-icon', ['name' => 'cart'])
                                    </svg>
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-red">{{ $cta->eyebrow }}</p>
                            </div>
                        @endif
                        @if ($cta->title)
                            <h2 class="mt-4 font-serif text-3xl font-bold uppercase leading-tight tracking-tight sm:text-4xl">{{ $cta->title }}</h2>
                        @endif
                        @if ($cta->body)
                            <p class="mt-4 text-sm leading-relaxed text-neutral-300 sm:text-base">{{ $cta->body }}</p>
                        @endif
                    </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                        @if ($cta->primary_label)
                            <a href="{{ $cta->resolvedUrl($cta->primary_url) }}" class="btn-primary w-full px-8 py-3 text-center sm:w-auto">{{ $cta->primary_label }}</a>
                        @endif
                        @if ($cta->secondary_label)
                            <a href="{{ $cta->resolvedUrl($cta->secondary_url) }}" class="hero-btn-outline w-full px-8 py-3 text-center sm:w-auto">{{ $cta->secondary_label }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($newArrivals?->is_active)
        <section class="bg-brand-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        @if ($newArrivals->eyebrow)
                            <p class="section-eyebrow">{{ $newArrivals->eyebrow }}</p>
                        @endif
                        @if ($newArrivals->title)
                            <h2 class="section-title mt-1">{{ $newArrivals->title }}</h2>
                        @endif
                        @if ($newArrivals->body)
                            <p class="mt-2 text-brand-muted">{{ $newArrivals->body }}</p>
                        @endif
                    </div>
                    @if ($newArrivals->primary_label)
                        <a href="{{ $newArrivals->resolvedUrl($newArrivals->primary_url) }}" class="text-sm font-medium text-brand-red transition hover:underline">
                            {{ $newArrivals->primary_label }} &rarr;
                        </a>
                    @endif
                </div>

                <div id="home-new-arrivals-grid" class="mt-10 grid grid-cols-2 gap-5 lg:grid-cols-4">
                    @forelse ($featuredProducts as $product)
                        @include('storefront.partials.product-card', ['product' => $product])
                    @empty
                        <p class="col-span-full text-brand-muted">Products will appear here once added in the admin dashboard.</p>
                    @endforelse
                </div>

                @include('storefront.partials.load-more-button', [
                    'hasMore' => $featuredProducts->hasMorePages(),
                    'url' => route('home.new-arrivals'),
                    'target' => '#home-new-arrivals-grid',
                    'nextPage' => $featuredProducts->currentPage() + 1,
                ])
            </div>
        </section>
    @endif

    @include('storefront.partials.testimonials', [
        'testimonials' => $testimonials,
        'header' => $sections->get(HomeSection::KEY_TESTIMONIALS_HEADER),
    ])

    @include('storefront.partials.delivery-notice', ['section' => $sections->get(HomeSection::KEY_DELIVERY_NOTICE)])
@endsection
