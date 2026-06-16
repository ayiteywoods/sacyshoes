@props(['href', 'active' => false, 'title' => null])

<a
    href="{{ $href }}"
    title="{{ $title }}"
    @click="mobileSidebarOpen = false"
    {{ $attributes->merge([
        'class' => 'admin-sidebar-link '.($active ? 'admin-sidebar-link-active' : ''),
    ]) }}
>
    @isset($icon)
        <span class="shrink-0">{{ $icon }}</span>
    @endisset
    <span class="admin-sidebar-text truncate">{{ $slot }}</span>
</a>
