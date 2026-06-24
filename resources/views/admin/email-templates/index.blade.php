@extends('layouts.admin')

@section('heading', 'Email templates')
@section('subheading', 'Customize customer emails sent by your store')

@section('content')
  @if ($templates->contains('slug', \App\Models\EmailTemplate::SLUG_PAYMENT_RECEIVED))
    <div class="card mb-6 overflow-hidden">
      <div class="border-b border-neutral-200 px-5 py-4">
        <h2 class="font-semibold">Recent invoice emails</h2>
        <p class="mt-1 text-sm text-brand-muted">Automatically sent to customers when payment is confirmed.</p>
      </div>

      @if ($recentInvoiceEmails->isEmpty())
        <div class="px-5 py-8 text-sm text-brand-muted">
          No invoice emails logged yet. They appear here after a customer pays successfully.
        </div>
      @else
        <div class="overflow-x-auto">
          <table class="admin-data-table">
            <thead>
              <tr>
                <th class="admin-table-cell text-left font-medium">Sent</th>
                <th class="admin-table-cell text-left font-medium">Recipient</th>
                <th class="admin-table-cell text-left font-medium">Order</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($recentInvoiceEmails as $dispatch)
                <tr>
                  <td class="admin-table-cell whitespace-nowrap">{{ $dispatch->sent_at->format('M j, Y g:i A') }}</td>
                  <td class="admin-table-cell">{{ $dispatch->recipient }}</td>
                  <td class="admin-table-cell whitespace-nowrap">
                    @if ($dispatch->order)
                      <a href="{{ route('admin.orders.show', $dispatch->order) }}" class="text-brand-red hover:underline">
                        {{ $dispatch->order->order_number }}
                      </a>
                    @else
                      <span class="text-brand-muted">—</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  @endif

  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach ($templates as $template)
      <div class="card p-5">
        <div class="flex items-start justify-between gap-3">
          <div>
            <h2 class="font-semibold">{{ $template->name }}</h2>
            @if ($template->description)
              <p class="mt-2 text-sm text-brand-muted">{{ $template->description }}</p>
            @endif
          </div>
        </div>

        <p class="mt-4 text-xs font-medium uppercase tracking-wide text-brand-muted">Subject</p>
        <p class="mt-1 text-sm">{{ $template->subject }}</p>

        <div class="mt-5 flex flex-wrap gap-2">
          <a href="{{ route('admin.email-templates.edit', $template) }}" class="btn-outline px-4 py-2 text-sm">
            Edit template
          </a>
          <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" class="btn-outline px-4 py-2 text-sm">
            Preview
          </a>
        </div>
      </div>
    @endforeach
  </div>
@endsection
