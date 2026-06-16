@php
    $section = $section ?? null;
@endphp

@if (! $section || $section->is_active)
    <section class="border-y border-neutral-200 bg-brand-white py-10">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 text-center sm:flex-row sm:justify-center sm:gap-8 sm:px-6 sm:text-left lg:px-8">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-red/10">
                <svg class="h-6 w-6 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <div>
                @if ($section?->title)
                    <h3 class="font-semibold uppercase tracking-wide text-brand-black">{{ $section->title }}</h3>
                @endif
                @if ($section?->body)
                    <p class="mt-1 text-sm text-brand-muted">{{ $section->body }}</p>
                @endif
            </div>
        </div>
    </section>
@endif
