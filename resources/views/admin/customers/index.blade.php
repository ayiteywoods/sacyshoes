@extends('layouts.admin')

@section('heading', 'Customers')

@section('content')
    <x-admin-table-panel :page-ids="$customers->pluck('id')">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="name" label="Name" class="admin-cell-primary" />
                    <x-admin-sort-th column="email" label="Email" class="admin-col-md" />
                    <x-admin-sort-th column="phone" label="Phone" class="admin-col-lg" />
                    <x-admin-sort-th column="orders_count" label="Orders" />
                    <x-admin-sort-th column="is_active" label="Status" class="admin-col-md" />
                    <th class="admin-table-cell admin-col-actions text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <x-admin-table-leading-cells :id="$customer->id" :number="$customers->firstItem() + $loop->index" />
                        <td class="admin-table-cell admin-cell-primary">
                            <div class="font-medium">{{ $customer->name }}</div>
                            <div class="mt-1 truncate text-xs text-brand-muted md:hidden">{{ $customer->email }}</div>
                            <div class="mt-1 text-xs text-brand-muted md:hidden">{{ $customer->is_active ? 'Active' : 'Inactive' }}</div>
                        </td>
                        <td class="admin-table-cell admin-col-md max-w-[12rem] truncate">{{ $customer->email }}</td>
                        <td class="admin-table-cell admin-col-lg whitespace-nowrap">{{ $customer->phone ?? '—' }}</td>
                        <td class="admin-table-cell whitespace-nowrap">{{ $customer->orders_count }}</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $customer->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="admin-table-cell admin-col-actions">
                            <div class="flex flex-col items-end gap-2 sm:flex-row sm:items-center">
                                <x-admin-table-actions
                                    :view-detail-url="route('admin.details.customers', $customer)"
                                />
                                <form method="POST" action="{{ route('admin.customers.toggle-status', $customer) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="whitespace-nowrap text-sm text-brand-red hover:underline">
                                        {{ $customer->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="admin-table-cell py-8 text-center text-brand-muted">No customers yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$customers" />
@endsection
