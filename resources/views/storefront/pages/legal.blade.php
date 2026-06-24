@extends('layouts.storefront')

@section('title', $page->title.' - SACYSHOES')

@section('content')
    @php
        $hero = $page->heroConfig();
        $sections = $page->tableOfContents();
        $relatedPages = collect()
            ->merge($footerLegalPages ?? collect())
            ->merge($footerCustomerCarePages ?? collect())
            ->reject(fn ($related) => $related->is($page))
            ->unique('id')
            ->values();
    @endphp

    <x-page-hero
        :eyebrow="$hero['eyebrow']"
        :title="$page->title"
        :description="$hero['description']"
        :icon="$hero['icon']"
        :stats="$hero['stats']"
    >
        <x-slot:actions>
            <a href="{{ route('pages.show', \App\Models\Page::SLUG_CONTACT) }}" class="btn-primary">Contact support</a>
            @if ($page->slug === \App\Models\Page::SLUG_PRIVACY)
                <a href="{{ route('pages.show', \App\Models\Page::SLUG_TERMS) }}" class="hero-btn-outline px-5 py-2.5">Terms &amp; conditions</a>
            @else
                <a href="{{ route('pages.show', \App\Models\Page::SLUG_PRIVACY) }}" class="hero-btn-outline px-5 py-2.5">Privacy policy</a>
            @endif
        </x-slot:actions>
    </x-page-hero>

    <section class="border-b border-neutral-200 bg-brand-light">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-brand-muted">
                    Please read this document carefully. It explains your rights and our responsibilities when you shop with {{ config('shop.store_name', "Sacy's Shoes") }}.
                </p>
                <p class="shrink-0 text-xs font-semibold uppercase tracking-wider text-brand-black">
                    Effective {{ $page->updated_at?->format('F j, Y') ?? '—' }}
                </p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="grid gap-10 xl:grid-cols-[220px_minmax(0,1fr)_280px]">
            @if ($sections !== [])
                <nav class="hidden xl:block" aria-label="On this page">
                    <div class="sticky top-28 border border-neutral-200 bg-white p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-red">On this page</p>
                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($sections as $section)
                                <li class="{{ $section['level'] === 3 ? 'pl-3' : '' }}">
                                    <a
                                        href="#{{ $section['id'] }}"
                                        class="block leading-snug text-brand-muted transition hover:text-brand-red {{ $section['level'] === 2 ? 'font-medium text-brand-black' : '' }}"
                                    >
                                        {{ $section['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </nav>
            @endif

            <article class="min-w-0 border border-neutral-200 bg-white p-6 sm:p-8 lg:p-10">
                <div class="policy-content">
                    {!! $page->renderedBody() !!}
                </div>
            </article>

            <aside class="space-y-4">
                @if ($sections !== [])
                    <div class="border border-neutral-200 bg-white p-5 xl:hidden">
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-red">Jump to section</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($sections as $section)
                                @if ($section['level'] === 2)
                                    <a
                                        href="#{{ $section['id'] }}"
                                        class="rounded-none border border-neutral-200 px-3 py-1.5 text-xs font-medium text-brand-black transition hover:border-brand-red hover:text-brand-red"
                                    >
                                        {{ $section['title'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="border border-neutral-200 bg-brand-light p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brand-black">Questions?</h2>
                    <p class="mt-3 text-sm leading-relaxed text-brand-muted">
                        If anything in this policy is unclear, our support team is happy to help.
                    </p>
                    <div class="mt-4 space-y-2 text-sm">
                        <a href="mailto:{{ $settings->contactPageEmail() }}" class="block font-medium text-brand-red transition hover:underline">
                            {{ $settings->contactPageEmail() }}
                        </a>
                        @foreach ($settings->contactPagePhones() as $phone)
                            <a href="{{ $settings->phoneTelUri($phone) }}" class="block text-brand-muted transition hover:text-brand-red">
                                {{ $phone }}
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('pages.show', \App\Models\Page::SLUG_CONTACT) }}" class="mt-4 inline-flex text-sm font-medium text-brand-red hover:underline">
                        Visit contact page
                    </a>
                </div>

                @if ($relatedPages->isNotEmpty())
                    <div class="border border-neutral-200 bg-white p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-brand-black">Related pages</h2>
                        <div class="mt-4 space-y-2 text-sm">
                            @foreach ($relatedPages->take(5) as $related)
                                <a href="{{ route('pages.show', $related) }}" class="block text-brand-muted transition hover:text-brand-red">
                                    {{ $related->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>
@endsection
