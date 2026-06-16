@extends('layouts.admin')

@section('heading', 'Orders')

@section('content')
    <x-admin-table-panel :page-ids="$orders->pluck('id')">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="order_number" label="Order" />
                    <x-admin-sort-th column="customer" label="Customer" class="admin-cell-primary" />
                    <x-admin-sort-th column="total" label="Total" />
                    <x-admin-sort-th column="payment_status" label="Payment" class="admin-col-md" />
                    <x-admin-sort-th column="status" label="Status" />
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
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $order->payment_status->label() }}</td>
                        <td class="admin-table-cell whitespace-nowrap">{{ $order->status->label() }}</td>
                        <td class="admin-table-cell admin-col-actions">
                            <x-admin-table-actions
                                :view-detail-url="route('admin.details.orders', $order)"
                                :edit-url="route('admin.orders.show', $order).'#order-status'"
                                :delete-url="route('admin.orders.destroy', $order)"
                                delete-confirm="Delete order {{ $order->order_number }}? This cannot be undone."
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="admin-table-cell py-8 text-center text-brand-muted">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$orders" />
@endsection
