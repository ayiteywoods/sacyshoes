@extends('layouts.admin')

@section('heading', 'Shipping regions')
@section('subheading', 'Manage regions and delivery options with prices')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-brand-muted">
            Regions listed here appear at checkout. Mark Accra as “Accra” to make delivery fee paid directly to rider.
        </div>
        <a href="{{ route('admin.shipping-regions.create') }}" class="btn-primary px-5 py-3">Add region</a>
    </div>

    <div class="mt-6 card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b border-neutral-200 bg-brand-white">
                <tr class="text-left text-xs uppercase tracking-wide text-brand-muted">
                    <th class="px-4 py-3">Region</th>
                    <th class="px-4 py-3">Accra</th>
                    <th class="px-4 py-3">Options</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($regions as $region)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $region->name }}</td>
                        <td class="px-4 py-3">
                            @if ($region->is_accra)
                                <span class="text-brand-red font-medium">Yes</span>
                            @else
                                No
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $region->options_count }}</td>
                        <td class="px-4 py-3">
                            @if ($region->is_active)
                                <span class="text-xs font-semibold uppercase tracking-wide text-brand-black">Active</span>
                            @else
                                <span class="text-xs font-semibold uppercase tracking-wide text-brand-muted">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.shipping-regions.edit', $region) }}" class="text-brand-red hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-brand-muted">No shipping regions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

