@extends('layouts.admin')

@section('heading', 'Dashboard')
@section('subheading', 'Overview of your store performance')

@section('content')
    {{-- Welcome + period filter --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm text-brand-muted">{{ now()->format('l, F j, Y') }}</p>
            <h2 class="mt-1 text-xl font-semibold">{{ $greeting }}, {{ auth()->user()->name }}</h2>
            <p class="mt-1 text-sm text-brand-muted">Here is what is happening with SACYSHOES {{ strtolower($periodLabel) }}.</p>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-2">
            @foreach (['today' => 'Today', '7d' => '7 days', '30d' => '30 days', 'month' => 'This month'] as $key => $label)
                <button
                    type="submit"
                    name="period"
                    value="{{ $key }}"
                    class="admin-period-pill {{ $period === $key ? 'admin-period-pill-active' : '' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </form>
    </div>

    {{-- Quick actions --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
        <a href="{{ route('admin.products.create') }}" class="admin-quick-action">
            <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Add product</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="admin-quick-action">
            <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c1.01.005 2.047.052 3.064.15 1.13.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-7.5z"/></svg>
            <span>View orders</span>
        </a>
        @adminCan('categories')
            <a href="{{ route('admin.categories.shop') }}" class="admin-quick-action">
                <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                <span>Shop categories</span>
            </a>
        @endadminCan
        @adminCan('content')
            <a href="{{ route('admin.email-templates.index') }}" class="admin-quick-action">
                <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                <span>Emails</span>
            </a>
            <a href="{{ route('admin.store-settings.edit') }}" class="admin-quick-action">
                <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Store settings</span>
            </a>
        @endadminCan
        <a href="{{ route('admin.reports.index') }}" class="admin-quick-action">
            <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            <span>Reports</span>
        </a>
        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="admin-quick-action">
            <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            <span>View store</span>
        </a>
        @adminCan('content')
            <a href="{{ route('about') }}" target="_blank" rel="noopener noreferrer" class="admin-quick-action">
                <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                <span>About page</span>
            </a>
        @endadminCan
    </div>

    @adminCan('content')
        <div class="card mt-6 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Storefront maintenance</h2>
                    <p class="mt-1 text-sm text-brand-muted">
                        Hide the shop from customers while you make updates. Admins can still access the admin panel and preview the storefront when logged in.
                    </p>
                    @if ($storeSettings->isMaintenanceModeEnabled())
                        <p class="mt-3 inline-flex items-center gap-2 rounded-none bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            Maintenance mode is currently ON
                        </p>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.maintenance.update') }}" class="w-full max-w-xl space-y-4">
                    @csrf
                    @method('PATCH')

                    <label class="flex items-start gap-3">
                        <input type="hidden" name="maintenance_mode" value="0">
                        <input
                            type="checkbox"
                            name="maintenance_mode"
                            value="1"
                            class="mt-1 rounded-none border-neutral-300 text-brand-red focus:ring-brand-red"
                            @checked(old('maintenance_mode', $storeSettings->maintenance_mode))
                        >
                        <span>
                            <span class="block text-sm font-medium">Enable maintenance mode</span>
                            <span class="mt-1 block text-xs text-brand-muted">Customers will see a maintenance page instead of the shop.</span>
                        </span>
                    </label>

                    <div>
                        <label for="maintenance_message" class="block text-sm font-medium">Maintenance message</label>
                        <textarea
                            id="maintenance_message"
                            name="maintenance_message"
                            rows="3"
                            class="input-field mt-1"
                            placeholder="{{ $storeSettings->maintenanceMessage() }}"
                        >{{ old('maintenance_message', $storeSettings->maintenance_message) }}</textarea>
                        @error('maintenance_message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn-primary">
                        {{ $storeSettings->isMaintenanceModeEnabled() ? 'Update maintenance settings' : 'Save maintenance settings' }}
                    </button>
                </form>
            </div>
        </div>
    @endadminCan

    {{-- Needs attention --}}
    @php
        $attentionTotal = $attention['pending_payment'] + $attention['needs_fulfillment'] + $attention['low_stock'] + $attention['failed_payments'];
    @endphp
    @if ($attentionTotal > 0)
        <div class="card mt-6 overflow-hidden">
            <x-admin-section-header title="Needs attention" subtitle="Items that may need action right now" />
            <div class="admin-attention-grid grid gap-px bg-neutral-200 sm:grid-cols-2 xl:grid-cols-4">
                @if ($attention['pending_payment'] > 0)
                    <a href="{{ route('admin.orders.index', ['sort' => 'created_at', 'direction' => 'desc']) }}" class="admin-attention-item">
                        <p class="text-2xl font-semibold text-amber-700">{{ $attention['pending_payment'] }}</p>
                        <p class="mt-1 text-sm font-medium">Pending payment</p>
                    </a>
                @endif
                @if ($attention['needs_fulfillment'] > 0)
                    <a href="{{ route('admin.orders.index') }}" class="admin-attention-item">
                        <p class="text-2xl font-semibold text-blue-700">{{ $attention['needs_fulfillment'] }}</p>
                        <p class="mt-1 text-sm font-medium">Awaiting fulfillment</p>
                    </a>
                @endif
                @if ($attention['low_stock'] > 0)
                    <a href="{{ route('admin.products.index', ['sort' => 'quantity', 'direction' => 'asc']) }}" class="admin-attention-item">
                        <p class="text-2xl font-semibold text-brand-red">{{ $attention['low_stock'] }}</p>
                        <p class="mt-1 text-sm font-medium">Low stock products</p>
                    </a>
                @endif
                @if ($attention['failed_payments'] > 0)
                    <a href="{{ route('admin.orders.index') }}" class="admin-attention-item">
                        <p class="text-2xl font-semibold text-red-700">{{ $attention['failed_payments'] }}</p>
                        <p class="mt-1 text-sm font-medium">Failed payments</p>
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if ($ordersToFulfill->total() > 0)
        <div class="card mt-6 overflow-hidden">
            <x-admin-section-header
                title="Orders to fulfill"
                subtitle="Paid orders waiting for processing or delivery updates"
                :href="route('admin.orders.index')"
            />
            <div class="divide-y divide-neutral-100">
                @foreach ($ordersToFulfill as $order)
                    <a href="{{ route('admin.orders.show', $order) }}#delivery-tracking" class="flex flex-col gap-3 px-4 py-4 text-sm transition hover:bg-brand-light sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $order->order_number }}</p>
                            <p class="text-brand-muted">{{ $order->user?->name ?? $order->billing_full_name }}</p>
                            <p class="mt-1 text-xs text-brand-muted">
                                Paid {{ $order->paid_at?->diffForHumans() ?? $order->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                            <x-admin-status-badge :status="$order->status" />
                            <span class="text-xs font-medium text-brand-red">Update tracking →</span>
                        </div>
                    </a>
                @endforeach
            </div>
            <x-admin-pagination :paginator="$ordersToFulfill" class="border-t border-neutral-100 px-4 py-3 sm:px-6" />
        </div>
    @endif

    {{-- Hero KPIs --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin-kpi-card
            :label="'Revenue · '.$periodLabel"
            :value="$periodStats['revenue']"
            :change="$comparison['revenue_change']"
            format="currency"
        />
        <x-admin-kpi-card
            :label="'Paid orders · '.$periodLabel"
            :value="$periodStats['orders']"
            :change="$comparison['orders_change']"
        />
        <x-admin-kpi-card
            label="Average order value"
            :value="$periodStats['average_order']"
            :change="$comparison['average_order_change']"
            format="currency"
        />
        <x-admin-kpi-card
            label="New customers"
            :value="$periodStats['new_customers']"
            :change="$comparison['customers_change']"
        />
    </div>

    {{-- Secondary KPIs --}}
    <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin-kpi-card label="Total revenue (all time)" :value="$stats['total_sales']" format="currency" />
        <x-admin-kpi-card label="Today's sales" :value="$stats['today_sales']" format="currency" />
        <x-admin-kpi-card label="Paid orders (all time)" :value="$stats['paid_orders']" />
        <x-admin-kpi-card label="Low stock" :value="$stats['low_stock_products']" highlight />
    </div>

    {{-- Status breakdowns --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="card overflow-hidden">
            <x-admin-section-header title="Order status" subtitle="Current order pipeline" :href="route('admin.orders.index')" />
            <div class="space-y-3 px-4 py-4 sm:px-6">
                @forelse ($orderStatusBreakdown as $row)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <x-admin-status-badge :status="$row['status']" />
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <div class="h-2 flex-1 bg-neutral-100">
                                @php $maxOrders = max($orderStatusBreakdown->max('count'), 1); @endphp
                                <div class="h-2 bg-brand-red" style="width: {{ round(($row['count'] / $maxOrders) * 100) }}%"></div>
                            </div>
                            <span class="w-8 text-right font-medium">{{ $row['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-brand-muted">No orders yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card overflow-hidden">
            <x-admin-section-header title="Payment health" subtitle="Payment status across all orders" :href="route('admin.orders.index')" />
            <div class="space-y-3 px-4 py-4 sm:px-6">
                @forelse ($paymentStatusBreakdown as $row)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <x-admin-status-badge :status="$row['status']" />
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <div class="h-2 flex-1 bg-neutral-100">
                                @php $maxPayments = max($paymentStatusBreakdown->max('count'), 1); @endphp
                                <div class="h-2 bg-brand-red" style="width: {{ round(($row['count'] / $maxPayments) * 100) }}%"></div>
                            </div>
                            <span class="w-8 text-right font-medium">{{ $row['count'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-sm text-brand-muted">No payment data yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notifications --}}
    <div class="card mt-8 overflow-hidden">
        <x-admin-section-header title="Notifications" subtitle="Store alerts requiring your attention">
            @if ($notifications->total() > 0)
                <form method="POST" action="{{ route('admin.notifications.destroy-all') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-brand-red hover:underline">Clear all</button>
                </form>
            @endif
        </x-admin-section-header>

        @forelse ($notifications as $notification)
            @include('admin.partials.notification-row', ['notification' => $notification])
        @empty
            <p class="px-4 py-8 text-sm text-brand-muted sm:px-6">No active notifications right now.</p>
        @endforelse

        <x-admin-pagination :paginator="$notifications" class="border-t border-neutral-100 px-4 py-3 sm:px-6" />
    </div>

    {{-- Charts --}}
    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <div class="card p-4 sm:p-6">
            <h2 class="font-semibold uppercase tracking-wide">Sales overview ({{ $periodLabel }})</h2>
            <p class="mt-1 text-sm text-brand-muted">Daily revenue from paid orders</p>
            <div class="mt-6 h-72">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <div class="card p-4 sm:p-6">
            <h2 class="font-semibold uppercase tracking-wide">Monthly revenue</h2>
            <p class="mt-1 text-sm text-brand-muted">Last 12 months</p>
            <div class="mt-6 h-72">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Activity timeline + new customers --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="card overflow-hidden lg:col-span-2">
            <x-admin-section-header title="Activity timeline" subtitle="Latest store events across orders, customers, and alerts" />
            <div class="divide-y divide-neutral-100">
                @forelse ($recentActivity as $activity)
                    @include('admin.partials.activity-row', ['activity' => $activity])
                @empty
                    <p class="px-4 py-8 text-sm text-brand-muted sm:px-6">No recent activity yet.</p>
                @endforelse
            </div>
            <x-admin-pagination :paginator="$recentActivity" class="border-t border-neutral-100 px-4 py-3 sm:px-6" />
        </div>

        <div class="card overflow-hidden">
            <x-admin-section-header
                :title="'New customers · '.$periodLabel"
                :href="route('admin.customers.index')"
            />
            <div class="divide-y divide-neutral-100">
                @forelse ($recentCustomers as $customer)
                    <div class="flex items-center justify-between gap-3 px-4 py-4 text-sm sm:px-6">
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ $customer->name }}</p>
                            <p class="truncate text-brand-muted">{{ $customer->email }}</p>
                            <p class="mt-1 text-xs text-brand-muted">
                                Joined {{ $customer->created_at->diffForHumans() }}
                                · {{ $customer->orders_count }} {{ str('order')->plural($customer->orders_count) }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 text-brand-red hover:underline"
                            @click="openDetail(@js(route('admin.details.customers', $customer)))"
                        >
                            View
                        </button>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center sm:px-6">
                        <p class="text-sm text-brand-muted">No new customers {{ strtolower($periodLabel) }}.</p>
                        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-block text-sm font-medium text-brand-red hover:underline">Share your store</a>
                    </div>
                @endforelse
            </div>
            <x-admin-pagination :paginator="$recentCustomers" class="border-t border-neutral-100 px-4 py-3 sm:px-6" />
        </div>
    </div>

    {{-- Recent orders + top products --}}
    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="card overflow-hidden lg:col-span-2">
            <x-admin-section-header title="Recent orders" :href="route('admin.orders.index')" />
            <div class="divide-y divide-neutral-100">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="flex flex-col gap-3 px-4 py-4 text-sm transition hover:bg-brand-light sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $order->order_number }}</p>
                            <p class="text-brand-muted">{{ $order->user?->name ?? $order->billing_full_name }}</p>
                            <p class="mt-1 text-xs text-brand-muted">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                            <x-admin-status-badge :status="$order->payment_status" />
                            <x-admin-status-badge :status="$order->status" />
                            <p class="font-medium">GHS {{ number_format($order->total, 2) }}</p>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center sm:px-6">
                        <p class="text-sm text-brand-muted">No orders yet.</p>
                        <a href="{{ route('admin.products.create') }}" class="mt-3 inline-block text-sm font-medium text-brand-red hover:underline">Add your first product</a>
                    </div>
                @endforelse
            </div>
            <x-admin-pagination :paginator="$recentOrders" class="border-t border-neutral-100 px-4 py-3 sm:px-6" />
        </div>

        <div class="card overflow-hidden">
            <x-admin-section-header title="Top selling products" :href="route('admin.reports.index')" />
            <div class="divide-y divide-neutral-100">
                @forelse ($topSelling as $product)
                    <div class="flex items-center justify-between gap-4 px-4 py-4 text-sm sm:px-6">
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ $product->product_name }}</p>
                            <p class="text-brand-muted">{{ $product->units_sold }} sold</p>
                        </div>
                        <p class="shrink-0 font-medium">GHS {{ number_format($product->revenue, 2) }}</p>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center sm:px-6">
                        <p class="text-sm text-brand-muted">No sales data yet.</p>
                    </div>
                @endforelse
            </div>
            <x-admin-pagination :paginator="$topSelling" class="border-t border-neutral-100 px-4 py-3 sm:px-6" />
        </div>
    </div>

    {{-- Low stock --}}
    @if ($lowStockProducts->total() > 0)
        <div class="card mt-8 overflow-hidden">
            <x-admin-section-header title="Low stock alerts" :href="route('admin.products.index', ['sort' => 'quantity', 'direction' => 'asc'])" />
            <div class="divide-y divide-neutral-100">
                @foreach ($lowStockProducts as $product)
                    <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center justify-between gap-4 px-4 py-4 text-sm transition hover:bg-brand-light sm:px-6">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $product->name }}</p>
                            <p class="text-brand-muted">{{ $product->category->name }}</p>
                        </div>
                        <p class="shrink-0 font-medium text-brand-red">{{ $product->quantity }} left</p>
                    </a>
                @endforeach
            </div>
            <x-admin-pagination :paginator="$lowStockProducts" class="border-t border-neutral-100 px-4 py-3 sm:px-6" />
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const brandRed = '#e10600';
        const daily = @json($dailyChart);
        const monthly = @json($monthlyChart);

        new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: {
                labels: daily.labels,
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: daily.revenue,
                    borderColor: brandRed,
                    backgroundColor: 'rgba(225, 6, 0, 0.08)',
                    fill: true,
                    tension: 0.35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });

        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'bar',
            data: {
                labels: monthly.labels,
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: monthly.revenue,
                    backgroundColor: brandRed,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });
    </script>
@endpush
