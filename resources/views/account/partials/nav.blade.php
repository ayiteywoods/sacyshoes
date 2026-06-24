<nav class="account-nav">
    <a
        href="{{ route('account.dashboard') }}"
        class="account-nav-link {{ request()->routeIs('account.dashboard') ? 'account-nav-link-active' : 'account-nav-link-inactive' }}"
    >
        Dashboard
    </a>
    <a
        href="{{ route('account.orders.index') }}"
        class="account-nav-link {{ request()->routeIs('account.orders.*') ? 'account-nav-link-active' : 'account-nav-link-inactive' }}"
    >
        My Orders
    </a>
    <a
        href="{{ route('account.favorites.index') }}"
        class="account-nav-link {{ request()->routeIs('account.favorites.*') ? 'account-nav-link-active' : 'account-nav-link-inactive' }}"
    >
        Favourites
    </a>
    <a
        href="{{ route('account.profile.edit') }}"
        class="account-nav-link {{ request()->routeIs('account.profile.*') ? 'account-nav-link-active' : 'account-nav-link-inactive' }}"
    >
        Profile Settings
    </a>
</nav>
