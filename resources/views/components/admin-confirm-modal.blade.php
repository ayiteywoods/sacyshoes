<div
    x-data="adminConfirmModal"
    x-cloak
    @keydown.escape.window="open && close()"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-brand-black/50 p-4"
        @click.self="close()"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="dialog"
            aria-modal="true"
            aria-labelledby="admin-confirm-title"
            class="w-full max-w-md border border-neutral-200 bg-brand-white shadow-xl"
        >
            <div class="border-b border-neutral-200 px-6 py-4">
                <h2 id="admin-confirm-title" class="text-sm font-semibold uppercase tracking-wide text-brand-black">
                    Confirm action
                </h2>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm leading-relaxed text-brand-black" x-text="message"></p>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-neutral-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" class="btn-outline w-full sm:w-auto" @click="close()">
                    Cancel
                </button>
                <button type="button" class="btn-primary w-full sm:w-auto" @click="confirm()">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>
