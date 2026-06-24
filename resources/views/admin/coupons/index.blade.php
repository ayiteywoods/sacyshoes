@extends('layouts.admin')

@section('heading', 'Coupons')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.coupons.create') }}" class="btn-primary w-full text-center sm:w-auto">Add coupon</a>
    </div>

    <x-admin-table-panel :page-ids="$coupons->pluck('id')">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="code" label="Code" />
                    <x-admin-sort-th column="type" label="Type" class="admin-col-md" />
                    <x-admin-sort-th column="value" label="Value" class="admin-col-md" />
                    <x-admin-sort-th column="used_count" label="Used" class="admin-col-md" />
                    <x-admin-sort-th column="expires_at" label="Expires" class="admin-col-md" />
                    <x-admin-sort-th column="is_active" label="Status" class="admin-col-md" />
                    <th class="admin-table-cell admin-col-actions text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coupons as $coupon)
                    <tr>
                        <x-admin-table-leading-cells :id="$coupon->id" :number="$coupons->firstItem() + $loop->index" />
                        <td class="admin-table-cell whitespace-nowrap font-medium">{{ $coupon->code }}</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $coupon->type->label() }}</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">
                            @if ($coupon->type->value === 'percent')
                                {{ rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') }}%
                            @else
                                GHS {{ number_format((float) $coupon->value, 2) }}
                            @endif
                        </td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">
                            {{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}
                        </td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $coupon->expires_at?->format('M j, Y') ?? '—' }}</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="admin-table-cell admin-col-actions">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn-outline px-3 py-1.5 text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete this coupon?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline px-3 py-1.5 text-xs text-brand-red">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="admin-table-cell py-8 text-center text-brand-muted">No coupons yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$coupons" />
@endsection
