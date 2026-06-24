@extends('layouts.storefront')

@section('title', $page->title.' - SACYSHOES')

@section('content')
    @php
        use Illuminate\Support\Str;

        $relatedPages = collect()
            ->merge($footerLegalPages ?? collect())
            ->merge($footerCustomerCarePages ?? collect())
            ->unique('id')
            ->values();

        $hero = $page->heroConfig();
    @endphp

    <x-page-hero
        :eyebrow="$hero['eyebrow']"
        :title="$page->title"
        :description="$hero['description']"
        :icon="$hero['icon']"
        :stats="$hero['stats']"
    />

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_320px]">
            <article class="card border border-neutral-100 bg-white p-6 sm:p-8">
                <header class="border-b border-neutral-100 pb-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-brand-red">Page details</p>
                    <p class="mt-2 text-sm text-brand-muted">
                        Last updated {{ $page->updated_at?->format('F j, Y') }}
                    </p>
                </header>

                <div class="prose prose-neutral mt-6 max-w-none prose-headings:tracking-tight prose-a:text-brand-red prose-a:no-underline hover:prose-a:underline">
                    {!! Str::markdown($page->body ?? '') !!}
                </div>
            </article>

            @if ($relatedPages->count() > 1)
                <aside class="space-y-4">
                    <div class="card border border-neutral-100 bg-white p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-brand-black">More pages</h2>
                        <div class="mt-4 space-y-2 text-sm">
                            @foreach ($relatedPages as $related)
                                <a
                                    href="{{ route('pages.show', $related) }}"
                                    class="flex items-center justify-between rounded-lg px-3 py-2 transition {{ $related->is($page) ? 'bg-neutral-100 text-brand-black' : 'text-brand-muted hover:bg-neutral-50 hover:text-brand-black' }}"
                                >
                                    <span class="font-medium">{{ $related->title }}</span>
                                    <span class="text-xs text-brand-muted">/pages/{{ $related->slug }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-5">
                        <p class="text-sm font-medium text-brand-black">Need help?</p>
                        <p class="mt-1 text-sm text-brand-muted">If you have questions about this policy, contact our support team.</p>
                        <a href="{{ route('pages.show', \App\Models\Page::SLUG_CONTACT) }}" class="mt-3 inline-flex text-sm font-medium text-brand-red hover:underline">
                            Contact support
                        </a>
                    </div>
                </aside>
            @endif
        </div>
    </div>
@endsection
