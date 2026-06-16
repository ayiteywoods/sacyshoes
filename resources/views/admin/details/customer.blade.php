<dl class="grid gap-4 sm:grid-cols-2">
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Name</dt>
        <dd class="mt-1 font-medium">{{ $user->name }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Email</dt>
        <dd class="mt-1">{{ $user->email }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Phone</dt>
        <dd class="mt-1">{{ $user->phone ?? '—' }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Status</dt>
        <dd class="mt-1">{{ $user->is_active ? 'Active' : 'Inactive' }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Total orders</dt>
        <dd class="mt-1">{{ $user->orders_count }}</dd>
    </div>
    <div>
        <dt class="text-xs font-medium uppercase tracking-wide text-brand-muted">Joined</dt>
        <dd class="mt-1">{{ $user->created_at->format('M j, Y') }}</dd>
    </div>
</dl>

@if ($recentOrders->isNotEmpty())
    <div class="mt-6">
        <h3 class="text-sm font-semibold uppercase tracking-wide">Recent orders</h3>
        <table class="mt-3 w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-200 text-left text-xs uppercase tracking-wide text-brand-muted">
                    <th class="py-2">Order</th>
                    <th class="py-2">Date</th>
                    <th class="py-2">Status</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @foreach ($recentOrders as $order)
                    <tr>
                        <td class="py-2 font-medium">{{ $order->order_number }}</td>
                        <td class="py-2">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="py-2">{{ $order->status->label() }}</td>
                        <td class="py-2 text-right">{{ config('shop.currency_symbol') }} {{ number_format($order->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
