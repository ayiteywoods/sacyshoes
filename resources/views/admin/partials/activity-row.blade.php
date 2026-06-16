@props(['activity'])

@php
    $iconClass = match ($activity['type']) {
        'order_paid' => 'admin-activity-icon-success',
        'order_placed', 'order' => 'admin-activity-icon-info',
        'customer_registered' => 'admin-activity-icon-brand',
        'stock' => 'admin-activity-icon-warning',
        'payment' => 'admin-activity-icon-warning',
        default => 'admin-activity-icon-neutral',
    };
@endphp

@if ($activity['url'])
    <a href="{{ $activity['url'] }}" class="admin-activity-item group">
@else
    <div class="admin-activity-item">
@endif
    <div class="admin-activity-icon {{ $iconClass }}">
        @switch($activity['type'])
            @case('order_paid')
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @break
            @case('customer_registered')
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                @break
            @case('stock')
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                @break
            @default
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c1.01.005 2.047.052 3.064.15 1.13.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-7.5z"/></svg>
        @endswitch
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-medium group-hover:text-brand-red">{{ $activity['title'] }}</p>
            <p class="text-xs text-brand-muted">{{ $activity['occurred_at']->diffForHumans() }}</p>
        </div>
        <p class="mt-1 text-sm text-brand-muted">{{ $activity['message'] }}</p>
    </div>
@if ($activity['url'])
    </a>
@else
    </div>
@endif
