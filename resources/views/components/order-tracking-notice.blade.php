@props([
    'compact' => false,
])

@auth
    <div {{ $attributes->merge(['class' => 'border border-neutral-200 bg-brand-light '.($compact ? 'p-4' : 'p-5')]) }}>
        <div class="flex gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-brand-red/10 text-brand-red">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-medium text-brand-black">Track your order progress</p>
                <p class="mt-1 text-sm leading-relaxed text-brand-muted">
                    View live updates for payment, processing, shipping, and delivery in your account.
                </p>
                @unless ($compact)
                    <a href="{{ route('account.orders.index') }}" class="mt-3 inline-flex text-sm font-medium text-brand-red transition hover:underline">
                        Go to My Orders &rarr;
                    </a>
                @endunless
            </div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'border border-neutral-200 bg-brand-light '.($compact ? 'p-4' : 'p-5')]) }}>
        <div class="flex gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-brand-red/10 text-brand-red">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-medium text-brand-black">Track your order after you log in</p>
                <p class="mt-1 text-sm leading-relaxed text-brand-muted">
                    Create an account or log in with the same email you used at checkout to follow your order from payment through to delivery.
                </p>
                @unless ($compact)
                    <div class="mt-3 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-brand-red transition hover:underline">Log in</a>
                        <a href="{{ route('register') }}" class="text-sm font-medium text-brand-black transition hover:underline">Create account</a>
                    </div>
                @endunless
            </div>
        </div>
    </div>
@endauth
