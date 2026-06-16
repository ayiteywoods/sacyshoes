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
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <a href="{{ route('admin.products.create') }}" class="admin-quick-action">
            <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>Add product</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="admin-quick-action">
            <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c1.01.005 2.047.052 3.064.15 1.13.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-7.5z"/></svg>
            <span>View orders</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="admin-quick-action">
            <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            <span>Reports</span>
        </a>
        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="admin-quick-action">
            <svg class="h-5 w-5 text-brand-red" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            <span>View store</span>
        </a>
    </div>

    {{-- Needs attention --}}
    @php
        $attentionTotal = $attention['pending_payment'] + $attention['needs_fulfillment'] + $attention['low_stock'] + $attention['failed_payments'];
    @endphp
    @if ($attentionTotal > 0)
        <div class="card mt-6 overflow-hidden">
            <x-admin-section-header title="Needs attention" subtitle="Items that may need action right now" />
            <div class="grid gap-px bg-neutral-200 sm:grid-cols-2 xl:grid-cols-4">
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
            @if ($notifications->isNotEmpty())
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
        </div>
    </div>

    {{-- Low stock --}}
    @if ($lowStockProducts->isNotEmpty())
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
