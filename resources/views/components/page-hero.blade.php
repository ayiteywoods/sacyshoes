@props([
    'eyebrow',
    'title',
    'description' => null,
    'icon' => 'info',
    'stats' => [],
])

<section {{ $attributes->merge(['class' => 'shop-hero relative hidden overflow-hidden border-b border-neutral-800 bg-brand-black text-white sm:block']) }}>
    <div class="shop-hero-glow shop-hero-glow-left" aria-hidden="true"></div>
    <div class="shop-hero-glow shop-hero-glow-right" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-14 lg:px-8">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2.5">
                    <span class="shop-hero-icon">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            @include('components.partials.hero-icon', ['name' => $icon])
                        </svg>
                    </span>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-red">{{ $eyebrow }}</p>
                </div>

                <h1 class="mt-4 font-serif text-4xl font-bold uppercase leading-tight tracking-tight sm:text-5xl">
                    {{ $title }}
                </h1>

                @if ($description)
                    <p class="mt-4 max-w-xl text-sm leading-relaxed text-neutral-300 sm:text-base">
                        {{ $description }}
                    </p>
                @endif

                @if (isset($chips))
                    <div class="mt-5 flex flex-wrap gap-2">
                        {{ $chips }}
                    </div>
                @endif

                @if (isset($actions))
                    <div class="mt-6 flex flex-wrap gap-3">
                        {{ $actions }}
                    </div>
                @endif
            </div>

            @if (count($stats) > 0)
                @php
                    $statColumns = match (min(count($stats), 3)) {
                        1 => 'sm:grid-cols-1',
                        2 => 'sm:grid-cols-2',
                        default => 'sm:grid-cols-3',
                    };
                @endphp
                <div class="grid w-full min-w-0 gap-3 {{ $statColumns }} lg:max-w-xl">
                    @foreach ($stats as $stat)
                        <x-page-hero-stat
                            :value="$stat['value']"
                            :label="$stat['label']"
                            :icon="$stat['icon'] ?? 'info'"
                            :tone="$stat['tone'] ?? 'red'"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
