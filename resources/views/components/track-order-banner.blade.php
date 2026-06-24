<div {{ $attributes->merge(['class' => 'track-order-banner w-full border-b border-brand-red-dark bg-brand-red']) }}>
    <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-3 px-4 py-3 sm:flex-row sm:items-center sm:px-6 lg:px-8">
        <div class="flex items-center gap-2.5 text-sm text-white">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center bg-white/15 text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <div>
                <p class="font-medium">Track your order</p>
                @auth
                    <p class="mt-0.5 text-white/90">Log in to your account to follow payment, processing, shipping, and delivery updates.</p>
                @else
                    <p class="mt-0.5 text-white/90">Already placed an order? Log in with your checkout email to follow your delivery progress.</p>
                @endauth
            </div>
        </div>

        @auth
            <a
                href="{{ route('account.orders.index') }}"
                class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-none border border-white bg-white px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-brand-red no-underline transition hover:bg-white/90"
            >
                Track my orders
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        @else
            <div class="flex shrink-0 flex-wrap items-center gap-3 sm:justify-end">
                <a
                    href="{{ route('login') }}"
                    class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-none border border-white bg-white px-5 py-2.5 text-xs font-semibold uppercase tracking-wide text-brand-red no-underline transition hover:bg-white/90"
                >
                    Log in to track
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="{{ route('register') }}" class="text-sm font-medium text-white/90 transition hover:text-white">
                    Create account
                </a>
            </div>
        @endauth
    </div>
</div>
