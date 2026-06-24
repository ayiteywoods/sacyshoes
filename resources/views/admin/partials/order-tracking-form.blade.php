@props(['order'])

<form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="space-y-3">
    @csrf
    @method('PATCH')

    <div>
        <label for="order-tracking-status-{{ $order->id }}" class="text-xs font-semibold uppercase tracking-wide text-brand-muted">
            Update delivery status
        </label>
        <select id="order-tracking-status-{{ $order->id }}" name="status" class="input-field mt-2">
            @foreach ($order->adminStatusOptions() as $status)
                <option value="{{ $status->value }}" @selected($order->status === $status)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <p class="text-xs text-brand-muted">
        Customers see this progress on their order tracking page and receive an email for each delivery stage update.
    </p>

    <button type="submit" class="btn-primary w-full sm:w-auto">
        Update tracking
    </button>
</form>
