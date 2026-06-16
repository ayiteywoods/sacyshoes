@props(['adminNotifications', 'adminNotificationCount'])

<div
    class="relative"
    @click.outside="notificationsOpen = false"
>
    <button
        type="button"
        @click="notificationsOpen = !notificationsOpen; userOpen = false"
        class="relative flex items-center justify-center rounded-none p-2 text-brand-black transition hover:bg-brand-light"
        :class="notificationsOpen ? 'bg-brand-light text-brand-red' : ''"
        aria-label="Notifications"
        :aria-expanded="notificationsOpen"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>

        @if ($adminNotificationCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center bg-brand-red px-1 text-[10px] font-semibold text-white">
                {{ $adminNotificationCount > 9 ? '9+' : $adminNotificationCount }}
            </span>
        @endif
    </button>

    <div
        x-show="notificationsOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="admin-notifications-menu"
    >
        <div class="flex items-center justify-between border-b border-neutral-200 px-5 py-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-brand-black">Notifications</p>
                <p class="mt-0.5 text-sm text-brand-muted">{{ $adminNotificationCount }} alert{{ $adminNotificationCount === 1 ? '' : 's' }}</p>
            </div>
            @if ($adminNotificationCount > 0)
                <form method="POST" action="{{ route('admin.notifications.destroy-all') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-medium uppercase tracking-wide text-brand-red hover:underline">
                        Clear all
                    </button>
                </form>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse ($adminNotifications as $notification)
                <div class="border-b border-neutral-100 px-5 py-4 {{ $notification->read_at ? 'bg-white' : 'bg-brand-light/40' }}">
                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-brand-black">{{ $notification->title }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-brand-muted">{{ $notification->message }}</p>
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
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-brand-muted">No new notifications.</p>
            @endforelse
        </div>
    </div>
</div>
