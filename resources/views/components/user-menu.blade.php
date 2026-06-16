@auth
    <div
        class="relative"
        @mouseenter="userOpen = true"
        @mouseleave="userOpen = false"
        @click.outside="userOpen = false"
    >
        <button
            type="button"
            @click="userOpen = !userOpen"
            class="flex items-center gap-2 rounded-none px-2 py-1.5 text-brand-black transition hover:bg-brand-light"
            :class="userOpen ? 'bg-brand-light text-brand-red' : ''"
            aria-label="Account menu"
            :aria-expanded="userOpen"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
            <span class="hidden max-w-[120px] truncate text-xs font-normal uppercase tracking-wide sm:inline">
                {{ auth()->user()->first_name ?? auth()->user()->name }}
            </span>
            <svg class="hidden h-3 w-3 shrink-0 sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>

        <div
            x-show="userOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            class="dropdown-menu"
        >
            <p class="border-b border-neutral-200 px-4 py-2 text-xs text-brand-muted">
                Signed in as<br>
                <span class="font-medium text-brand-black">{{ auth()->user()->email }}</span>
            </p>

            <a href="{{ route('account.dashboard') }}" class="dropdown-item">My Account</a>
            <a href="{{ route('account.orders.index') }}" class="dropdown-item">My Orders</a>
            <a href="{{ route('account.profile.edit') }}" class="dropdown-item">Profile Settings</a>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">Admin Dashboard</a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item w-full text-left">Logout</button>
            </form>
        </div>
    </div>
@else
    <div class="flex items-center gap-2">
        <a href="{{ route('login') }}" class="nav-link">Login</a>
        <a href="{{ route('register') }}" class="btn-primary">Register</a>
    </div>
@endauth
