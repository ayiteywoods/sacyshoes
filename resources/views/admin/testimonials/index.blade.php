@extends('layouts.admin')

@section('heading', 'Testimonials')

@section('content')
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.testimonials.create') }}" class="btn-primary w-full text-center sm:w-auto">Add testimonial</a>
    </div>

    <x-admin-table-panel :page-ids="$testimonials->pluck('id')">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <x-admin-table-leading-header />
                    <x-admin-sort-th column="author_name" label="Customer" />
                    <th class="admin-table-cell admin-cell-primary font-medium">Quote</th>
                    <x-admin-sort-th column="rating" label="Rating" class="admin-col-md" />
                    <x-admin-sort-th column="is_active" label="Status" class="admin-col-md" />
                    <th class="admin-table-cell admin-col-actions text-right font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($testimonials as $testimonial)
                    <tr>
                        <x-admin-table-leading-cells :id="$testimonial->id" :number="$testimonials->firstItem() + $loop->index" />
                        <td class="admin-table-cell whitespace-nowrap font-medium">{{ $testimonial->author_name }}</td>
                        <td class="admin-table-cell admin-cell-primary">{{ Str::limit($testimonial->quote, 80) }}</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $testimonial->rating }}/5</td>
                        <td class="admin-table-cell admin-col-md whitespace-nowrap">{{ $testimonial->is_active ? 'Active' : 'Hidden' }}</td>
                        <td class="admin-table-cell admin-col-actions">
                            <x-admin-table-actions
                                :edit-url="route('admin.testimonials.edit', $testimonial)"
                                :delete-url="route('admin.testimonials.destroy', $testimonial)"
                                delete-confirm="Delete this testimonial?"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="admin-table-cell py-8 text-center text-brand-muted">No testimonials yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin-table-panel>

    <x-admin-pagination :paginator="$testimonials" />
@endsection
