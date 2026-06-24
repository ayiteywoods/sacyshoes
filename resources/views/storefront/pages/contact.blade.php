@extends('layouts.storefront')

@section('title', $page->title.' - SACYSHOES')

@section('content')
    @php
        use Illuminate\Support\Str;

        $hero = $page->heroConfig();
        $phones = $settings->contactPagePhones();
        $email = $settings->contactPageEmail();
        $address = $settings->contactPageAddress();
        $mapsUrl = filled($address)
            ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($address)
            : null;

        $whatsappLink = collect($socialLinks ?? [])->firstWhere('platform', 'whatsapp');
    @endphp

    <x-page-hero
        :eyebrow="$hero['eyebrow']"
        :title="$page->title"
        :description="$hero['description']"
        :icon="$hero['icon']"
        :stats="$hero['stats']"
    >
        <x-slot:actions>
            <a href="mailto:{{ $email }}" class="btn-primary">Email support</a>
            @if ($phones !== [])
                <a href="{{ $settings->phoneTelUri($phones[0]) }}" class="hero-btn-outline px-5 py-2.5">Call us</a>
            @endif
        </x-slot:actions>
    </x-page-hero>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_300px]">
            <div>
                <div class="prose prose-neutral max-w-none prose-p:text-lg prose-p:leading-relaxed prose-p:text-brand-muted">
                    {!! Str::markdown($page->body ?? '') !!}
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2">
                    <div class="group border border-neutral-200 bg-white p-6 transition hover:border-brand-red/30 hover:shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-red/10 text-brand-red">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <h2 class="mt-5 text-sm font-semibold uppercase tracking-wider text-brand-black">Email</h2>
                        <p class="mt-2 text-sm text-brand-muted">For order updates, sizing help, and general enquiries.</p>
                        <a href="mailto:{{ $email }}" class="mt-4 inline-block text-base font-medium text-brand-red transition hover:underline">
                            {{ $email }}
                        </a>
                    </div>

                    <div class="group border border-neutral-200 bg-white p-6 transition hover:border-brand-red/30 hover:shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-red/10 text-brand-red">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                        </div>
                        <h2 class="mt-5 text-sm font-semibold uppercase tracking-wider text-brand-black">Phone</h2>
                        <p class="mt-2 text-sm text-brand-muted">Speak with our team during business hours.</p>
                        <ul class="mt-4 space-y-2">
                            @foreach ($phones as $phone)
                                <li>
                                    <a href="{{ $settings->phoneTelUri($phone) }}" class="text-base font-medium text-brand-red transition hover:underline">
                                        {{ $phone }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="group border border-neutral-200 bg-white p-6 transition hover:border-brand-red/30 hover:shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-red/10 text-brand-red">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                        <h2 class="mt-5 text-sm font-semibold uppercase tracking-wider text-brand-black">Visit us</h2>
                        <p class="mt-2 text-sm text-brand-muted">Drop by our store for fittings and in-person support.</p>
                        @if ($mapsUrl)
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 block text-base font-medium leading-relaxed text-brand-black transition hover:text-brand-red">
                                {{ $address }}
                                <span class="mt-1 block text-sm font-normal text-brand-red">Open in Google Maps</span>
                            </a>
                        @else
                            <p class="mt-4 text-base font-medium leading-relaxed text-brand-black">{{ $address }}</p>
                        @endif
                    </div>

                    <div class="group border border-neutral-200 bg-white p-6 transition hover:border-brand-red/30 hover:shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-red/10 text-brand-red">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="mt-5 text-sm font-semibold uppercase tracking-wider text-brand-black">Business hours</h2>
                        <p class="mt-2 text-sm text-brand-muted">We're available most days of the week.</p>
                        <p class="mt-4 text-base font-medium text-brand-black">{{ $settings->contactPageHoursDays() }}</p>
                        <p class="mt-1 text-sm text-brand-muted">{{ $settings->contactPageHoursTime() }}</p>
                        <p class="mt-3 text-sm text-brand-muted">{{ $settings->contactPageHoursNote() }}</p>
                    </div>
                </div>

                @if ($whatsappLink)
                    <div class="mt-8 flex flex-col gap-4 border border-neutral-200 bg-brand-light p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-brand-black">Prefer WhatsApp?</p>
                            <p class="mt-1 text-sm text-brand-muted">Message us directly for a faster reply on mobile.</p>
                        </div>
                        <a href="{{ $whatsappLink['url'] }}" target="_blank" rel="noopener noreferrer" class="btn-primary shrink-0 text-center">
                            Chat on WhatsApp
                        </a>
                    </div>
                @endif
            </div>

            <aside class="space-y-4">
                <div class="border border-neutral-200 bg-white p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brand-black">Before you reach out</h2>
                    <p class="mt-3 text-sm leading-relaxed text-brand-muted">
                        Many questions are answered in our help pages. You may find what you need right away.
                    </p>
                    <div class="mt-4 space-y-2 text-sm">
                        <a href="{{ route('pages.show', \App\Models\Page::SLUG_DELIVERY) }}" class="block text-brand-muted transition hover:text-brand-red">Delivery info</a>
                        <a href="{{ route('pages.show', \App\Models\Page::SLUG_RETURNS) }}" class="block text-brand-muted transition hover:text-brand-red">Returns policy</a>
                        <a href="{{ route('shop.index') }}" class="block text-brand-muted transition hover:text-brand-red">Browse the shop</a>
                    </div>
                </div>

                <div class="border border-neutral-200 bg-brand-light p-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-brand-black">Order support</h2>
                    <p class="mt-3 text-sm leading-relaxed text-brand-muted">
                        Please include your order number when contacting us about an existing purchase so we can assist you faster.
                    </p>
                </div>
            </aside>
        </div>
    </section>
@endsection
