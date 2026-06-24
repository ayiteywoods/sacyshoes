@extends('layouts.admin')

@section('heading', 'Orders')

@section('content')
    @php
        use App\Enums\OrderStatus;
        use App\Enums\PaymentStatus;

        $filterOptions = [
            '' => 'All orders',
            PaymentStatus::Paid->value => 'Paid',
            PaymentStatus::Pending->value => 'Pending',
            OrderStatus::Cancelled->value => 'Cancelled',
        ];
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap gap-2">
            @foreach (request()->except(['payment_status', 'page']) as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $item)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            @foreach ($filterOptions as $value => $label)
                <button
                    type="submit"
                    name="payment_status"
                    value="{{ $value }}"
                    class="admin-period-pill {{ ($paymentFilter ?? '') === $value ? 'admin-period-pill-active' : '' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </form>

        <p class="text-sm text-brand-muted">
            @if (($paymentFilter ?? '') === PaymentStatus::Paid->value)
                Showing paid orders only
            @elseif (($paymentFilter ?? '') === PaymentStatus::Pending->value)
                Showing pending payment orders only
            @elseif (($paymentFilter ?? '') === OrderStatus::Cancelled->value)
                Showing cancelled orders only
            @else
                Showing all orders
            @endif
        </p>
    </div>

    <x-admin-table-panel :page-ids="$orders->pluck('id')">
        <x-slot:bulkActions>
            <form
                method="POST"
                action="{{ route('admin.orders.invoices.export') }}"
                class="inline"
                @submit.prevent="if (!canExportInvoices) return; appendSelectedToForm($el); $el.submit();"
            >
                @csrf
                <button
                    type="submit"
                    class="btn-outline px-3 py-1.5 text-xs sm:text-sm"
                    :disabled="!canExportInvoices"
                    :class="{ 'opacity-50 cursor-not-allowed': !canExportInvoices }"
                >
                    Download PDFs
                </button>
            </form>

            <form
                method="POST"
                action="{{ route('admin.orders.invoices.print') }}"
                target="_blank"
                class="inline"
                @submit.prevent="if (!canExportInvoices) return; appendSelectedToForm($el); $el.submit();"
            >
                @csrf
                <button
                    type="submit"
                    class="btn-primary px-3 py-1.5 text-xs sm:text-sm"
                    :disabled="!canExportInvoices"
                    :class="{ 'opacity-50 cursor-not-allowed': !canExportInvoices }"
                >
                    Print invoices
                </button>
            </form>
        </x-slot:bulkActions>

        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="order_number" label="Order" />
                    <x-admin-sort-th column="customer" label="Customer" class="admin-cell-primary" />
                    <x-admin-sort-th column="total" label="Total" />
                    <x-admin-sort-th column="payment_status" label="Payment" class="admin-col-md" />
                    <x-admin-sort-th column="status" label="Status" />
                    <x-admin-sort-th column="created_at" label="Date & time" class="admin-col-md" />
                    <th class="admin-table-cell admin-col-actions text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <x-admin-table-leading-cells :id="$order->id" :number="$orders->firstItem() + $loop->index" />
                        <td class="admin-table-cell whitespace-nowrap font-medium">{{ $order->order_number }}</td>
                        <td class="admin-table-cell admin-cell-primary">{{ $order->user?->name ?? $order->billing_full_name }}</td>
                        <td class="admin-table-cell whitespace-nowrap">GHS {{ number_format($order->total, 2) }}</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">
                            <x-admin-status-badge :status="$order->payment_status" />
                        </td>
                        <td class="admin-table-cell whitespace-nowrap">{{ $order->status->label() }}</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">
                            <div>{{ $order->created_at->format('M j, Y') }}</div>
                            <div class="text-xs text-brand-muted">{{ $order->created_at->format('g:i A') }}</div>
                        </td>
                        <td class="admin-table-cell admin-col-actions">
                            <x-admin-table-actions
                                :view-detail-url="route('admin.details.orders', $order)"
                                :edit-url="route('admin.orders.show', $order).'#delivery-tracking'"
                                edit-title="Update tracking"
                                :delete-url="route('admin.orders.destroy', $order)"
                                delete-confirm="Delete order {{ $order->order_number }}? This cannot be undone."
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="admin-table-cell py-8 text-center text-brand-muted">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$orders" />
@endsection
