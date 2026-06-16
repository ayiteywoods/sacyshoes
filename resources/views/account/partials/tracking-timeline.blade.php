@props(['steps'])

<ol class="space-y-0">
    @foreach ($steps as $index => $step)
        <li class="relative flex gap-4 pb-8 last:pb-0">
            @if (! $loop->last)
                <span
                    class="absolute left-[11px] top-6 h-full w-px {{ $step['completed'] ? 'bg-brand-red' : 'bg-neutral-200' }}"
                    aria-hidden="true"
                ></span>
            @endif

            <span
                class="relative z-10 mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 text-xs font-semibold
                    {{ $step['current'] ? 'border-brand-red bg-brand-red text-white' : ($step['completed'] ? 'border-brand-red bg-brand-red text-white' : 'border-neutral-300 bg-white text-brand-muted') }}"
            >
                @if ($step['completed'] && ! $step['current'])
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @else
                    {{ $index + 1 }}
                @endif
            </span>

            <div class="min-w-0 pt-0.5">
                <p class="font-medium {{ $step['current'] ? 'text-brand-red' : 'text-brand-black' }}">
                    {{ $step['label'] }}
                </p>
                @if ($step['date'])
                    <p class="mt-1 text-sm text-brand-muted">
                        {{ $step['date']->format('M j, Y g:i A') }}
                    </p>
                @endif
            </div>
        </li>
    @endforeach
</ol>
