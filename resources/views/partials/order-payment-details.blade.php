@php($payment = $order->payment)

@if ($payment)
    <dl class="space-y-3 text-sm">
        <div>
            <dt class="text-brand-muted">Payment method</dt>
            <dd class="font-medium">{{ $order->paymentMethodLabel() }}</dd>
        </div>
        <div>
            <dt class="text-brand-muted">Payment status</dt>
            <dd class="font-medium">{{ $order->payment_status->label() }}</dd>
        </div>
        @if ($payment->paystackTransactionId())
            <div>
                <dt class="text-brand-muted">Paystack reference</dt>
                <dd class="font-medium break-all">{{ $order->order_number }}-{{ $payment->paystackTransactionId() }}</dd>
            </div>
            <div>
                <dt class="text-brand-muted">Paystack transaction ID</dt>
                <dd class="font-medium break-all">{{ $payment->paystackTransactionId() }}</dd>
            </div>
        @endif
        @if ($payment->paystackChannel())
            <div>
                <dt class="text-brand-muted">Payment channel</dt>
                <dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $payment->paystackChannel())) }}</dd>
            </div>
        @endif
        @if ($payment->paid_at)
            <div>
                <dt class="text-brand-muted">Paid at</dt>
                <dd class="font-medium">{{ $payment->paid_at->format('M j, Y g:i A') }}</dd>
            </div>
        @endif
    </dl>
@else
    <p class="text-sm text-brand-muted">No payment record yet.</p>
@endif
