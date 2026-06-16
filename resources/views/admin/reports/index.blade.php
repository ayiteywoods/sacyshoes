@extends('layouts.admin')

@section('heading', 'Sales Reports')
@section('subheading', 'Daily and monthly performance with exports')

@section('content')
    <div class="card p-4 sm:p-6">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-4 md:grid-cols-4 md:items-end">
            <div>
                <label for="from" class="block text-sm font-medium">From</label>
                <input id="from" type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input-field">
            </div>
            <div>
                <label for="to" class="block text-sm font-medium">To</label>
                <input id="to" type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input-field">
            </div>
            <div>
                <button type="submit" class="btn-primary w-full py-2.5">Apply</button>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('admin.reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'format' => 'csv']) }}" class="btn-outline w-full py-2.5 text-center">
                    Export CSV
                </a>
                <a href="{{ route('admin.reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d'), 'format' => 'pdf']) }}" target="_blank" class="btn-outline w-full py-2.5 text-center">
                    Export PDF
                </a>
            </div>
        </form>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-4 sm:p-5">
            <p class="text-xs uppercase tracking-wide text-brand-muted">Revenue</p>
            <p class="mt-2 text-2xl font-semibold text-brand-red">GHS {{ number_format($summary['revenue'], 2) }}</p>
        </div>
        <div class="card p-4 sm:p-5">
            <p class="text-xs uppercase tracking-wide text-brand-muted">Orders</p>
            <p class="mt-2 text-2xl font-semibold">{{ $summary['orders'] }}</p>
        </div>
        <div class="card p-4 sm:p-5">
            <p class="text-xs uppercase tracking-wide text-brand-muted">Transactions</p>
            <p class="mt-2 text-2xl font-semibold">{{ $summary['transactions'] }}</p>
        </div>
        <div class="card p-4 sm:p-5">
            <p class="text-xs uppercase tracking-wide text-brand-muted">Growth</p>
            <p class="mt-2 text-2xl font-semibold">
                @if ($growth === null)
                    —
                @else
                    {{ $growth > 0 ? '+' : '' }}{{ $growth }}%
                @endif
            </p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin-table-panel :page-ids="$orders->pluck('id')">
                <div class="border-b border-neutral-200 px-4 py-4 sm:px-6">
                    <h2 class="font-semibold">Orders in period</h2>
                </div>
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <x-admin-table-leading-header />
                            <x-admin-sort-th column="order_number" label="Order" />
                            <x-admin-sort-th column="customer" label="Customer" class="admin-cell-primary" />
                            <x-admin-sort-th column="paid_at" label="Date" class="admin-col-md" />
                            <x-admin-sort-th column="total" label="Total" />
                            <x-admin-sort-th column="status" label="Status" class="admin-col-md" />
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <x-admin-table-leading-cells :id="$order->id" :number="$orders->firstItem() + $loop->index" />
                                <td class="admin-table-cell whitespace-nowrap">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-brand-red hover:underline">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="admin-table-cell admin-cell-primary">{{ $order->user?->name ?? $order->billing_full_name }}</td>
                                <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $order->paid_at?->format('M j, Y') }}</td>
                                <td class="admin-table-cell whitespace-nowrap">GHS {{ number_format($order->total, 2) }}</td>
                                <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $order->status->label() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="admin-table-cell py-8 text-center text-brand-muted">No paid orders in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin-table-panel>

            <x-admin-pagination :paginator="$orders" />
        </div>

        <div>
            <x-admin-table-panel :page-ids="$topSelling->pluck('product_name')">
                <div class="border-b border-neutral-200 px-4 py-4 sm:px-6">
                    <h2 class="font-semibold">Top products</h2>
                </div>
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <x-admin-table-leading-header />
                            <x-admin-sort-th column="product_name" label="Product" class="admin-cell-primary" sort-key="product_sort" direction-key="product_direction" page-key="product_page" />
                            <x-admin-sort-th column="units_sold" label="Sold" sort-key="product_sort" direction-key="product_direction" page-key="product_page" />
                            <x-admin-sort-th column="revenue" label="Revenue" align="right" sort-key="product_sort" direction-key="product_direction" page-key="product_page" />
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topSelling as $product)
                            <tr>
                                <x-admin-table-leading-cells :id="$product->product_name" :number="$topSelling->firstItem() + $loop->index" />
                                <td class="admin-table-cell admin-cell-primary font-medium">{{ $product->product_name }}</td>
                                <td class="admin-table-cell whitespace-nowrap text-brand-muted">{{ $product->units_sold }} sold</td>
                                <td class="admin-table-cell whitespace-nowrap text-right">GHS {{ number_format($product->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="admin-table-cell py-8 text-center text-brand-muted">No product sales yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin-table-panel>

            <x-admin-pagination :paginator="$topSelling" />
        </div>
    </div>
@endsection
