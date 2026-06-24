@props([
    'hasMore' => false,
    'url',
    'target',
    'nextPage' => 2,
])

@if ($hasMore)
    <div class="mt-10 text-center" data-load-more-wrapper>
        <button
            type="button"
            class="btn-outline px-8 py-3"
            data-load-more
            data-url="{{ $url }}"
            data-target="{{ $target }}"
            data-page="{{ $nextPage }}"
        >
            Load more
        </button>
    </div>
@endif
