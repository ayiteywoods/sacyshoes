@props(['notification'])

<div class="flex items-start gap-4 border-b border-neutral-100 px-6 py-4 last:border-b-0 {{ $notification->read_at ? '' : 'bg-brand-light/30' }}">
    <div class="min-w-0 flex-1">
        <p class="font-medium text-brand-black">{{ $notification->title }}</p>
        <p class="mt-1 text-sm text-brand-muted">{{ $notification->message }}</p>
        <p class="mt-2 text-xs text-brand-muted">{{ $notification->created_at->diffForHumans() }}</p>
    </div>

    <div class="flex shrink-0 items-center gap-1">
        <a
            href="{{ route('admin.notifications.show', $notification) }}"
            class="notification-action-btn"
            title="View"
            aria-label="View notification"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>

        <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="notification-action-btn notification-action-btn-danger"
                title="Dismiss"
                aria-label="Dismiss notification"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
            </button>
        </form>
    </div>
</div>
