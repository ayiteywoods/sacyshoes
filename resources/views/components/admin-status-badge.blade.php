@props(['status'])

@php
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;

    $label = $status->label();
    $tone = 'neutral';

    if ($status instanceof OrderStatus) {
        $tone = match ($status) {
            OrderStatus::PendingPayment => 'warning',
            OrderStatus::Paid, OrderStatus::Processing, OrderStatus::ReadyForDelivery, OrderStatus::Shipped => 'info',
            OrderStatus::Delivered => 'success',
            OrderStatus::Cancelled, OrderStatus::Refunded => 'danger',
        };
    }

    if ($status instanceof PaymentStatus) {
        $tone = match ($status) {
            PaymentStatus::Pending => 'warning',
            PaymentStatus::Paid => 'success',
            PaymentStatus::Failed => 'danger',
            PaymentStatus::Refunded => 'neutral',
        };
    }

    $toneClass = match ($tone) {
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        default => 'bg-neutral-100 text-neutral-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-none px-2 py-0.5 text-xs font-medium {$toneClass}"]) }}>
    {{ $label }}
</span>
