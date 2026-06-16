<div
    x-show="detailDrawerOpen"
    x-cloak
    class="admin-detail-drawer"
    @keydown.escape.window="detailDrawerOpen = false"
>
    <div
        class="admin-detail-backdrop"
        x-show="detailDrawerOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="detailDrawerOpen = false"
    ></div>

    <aside
        class="admin-detail-panel"
        x-show="detailDrawerOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        @click.stop
    >
        <div class="admin-detail-toolbar no-print">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wide text-brand-muted">Details</p>
                <h2 class="truncate text-lg font-semibold" x-text="detailTitle"></h2>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <button type="button" class="btn-outline px-3 py-2 text-xs" @click="printDetail()" :disabled="detailLoading">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18M6.34 6h11.32c1.12 0 2.04.92 2.04 2.04v7.92c0 1.12-.92 2.04-2.04 2.04H6.34c-1.12 0-2.04-.92-2.04-2.04V8.04C4.3 6.92 5.22 6 6.34 6z"/>
                        </svg>
                        Print
                    </span>
                </button>
                <button type="button" class="admin-action-btn" @click="detailDrawerOpen = false" aria-label="Close drawer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="admin-detail-body" id="admin-detail-print-area">
            <div x-show="detailLoading" class="flex items-center justify-center py-20 text-sm text-brand-muted">
                Loading details...
            </div>

            <div x-show="!detailLoading" x-cloak>
                @include('admin.details.partials.letterhead')
                <div class="admin-detail-content" x-html="detailHtml"></div>
                <p class="mt-8 border-t border-neutral-200 pt-4 text-xs text-brand-muted">
                    Printed on {{ now()->format('M j, Y g:i A') }} · {{ config('shop.store_name') }}
                </p>
            </div>
        </div>
    </aside>
</div>
