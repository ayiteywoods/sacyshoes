@if ($order->payment_status === \App\Enums\PaymentStatus::Paid)
    <div @class([
        'flex flex-col gap-2 sm:flex-row',
        $class ?? null,
    ])>
        <a
            href="{{ route('admin.orders.invoice', $order) }}"
            class="btn-outline w-full text-center sm:w-auto"
            target="_blank"
            rel="noopener"
        >
            View invoice
        </a>
        <a
            href="{{ route('admin.orders.invoice.pdf', $order) }}"
            class="btn-primary w-full text-center sm:w-auto"
        >
            Download PDF
        </a>
    </div>
@endif
