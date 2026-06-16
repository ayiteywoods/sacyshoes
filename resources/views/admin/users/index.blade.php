@extends('layouts.admin')

@section('heading', 'Admin users')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.users.create') }}" class="btn-primary w-full text-center sm:w-auto">Add admin user</a>
    </div>

    <x-admin-table-panel :page-ids="$users->pluck('id')">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="name" label="Name" class="admin-cell-primary" />
                    <x-admin-sort-th column="email" label="Email" class="admin-col-md" />
                    <x-admin-sort-th column="phone" label="Phone" class="admin-col-lg" />
                    <x-admin-sort-th column="is_active" label="Status" class="admin-col-md" />
                    <th class="admin-table-cell admin-col-lg font-medium">Access</th>
                    <th class="admin-table-cell admin-col-actions text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <x-admin-table-leading-cells :id="$user->id" :number="$users->firstItem() + $loop->index" />
                        <td class="admin-table-cell admin-cell-primary font-medium">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="text-xs text-brand-muted">(You)</span>
                            @endif
                        </td>
                        <td class="admin-table-cell admin-col-md">{{ $user->email }}</td>
                        <td class="admin-table-cell admin-col-lg whitespace-nowrap">{{ $user->phone ?? '—' }}</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="admin-table-cell admin-col-lg whitespace-nowrap text-sm text-brand-muted">
                            @if ($user->isSuperAdmin())
                                Full access
                            @else
                                {{ count($user->admin_permissions ?? []) }} permissions
                            @endif
                        </td>
                        <td class="admin-table-cell admin-col-actions">
                            <x-admin-table-actions
                                :edit-url="route('admin.users.edit', $user)"
                                :delete-url="route('admin.users.destroy', $user)"
                                delete-confirm="Remove this admin user?"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="admin-table-cell py-8 text-center text-brand-muted">No admin users yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$users" />
@endsection
