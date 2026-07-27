<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaystackPaymentReconciliationService
{
    public function __construct(
        protected PaystackService $paystack,
        protected OrderPaymentService $orderPayments,
    ) {}

    /**
     * @return array{reconciled: bool, reason: string}
     */
    public function reconcilePayment(Payment $payment, bool $verbose = false): array
    {
        if ($payment->provider !== 'paystack') {
            return ['reconciled' => false, 'reason' => 'not a Paystack payment'];
        }

        $order = $payment->order;

        if (! $order) {
            return ['reconciled' => false, 'reason' => 'order not found'];
        }

        if ($order->payment_status === PaymentStatus::Paid) {
            return ['reconciled' => true, 'reason' => 'order already paid'];
        }

        if ($order->payment_status === PaymentStatus::Refunded) {
            return ['reconciled' => false, 'reason' => 'order refunded'];
        }

        if ($payment->status === PaymentStatus::Paid) {
            $this->orderPayments->markAsPaid($order, $payment, (array) data_get($payment->metadata, 'verification', []));

            return [
                'reconciled' => $order->fresh()->payment_status === PaymentStatus::Paid,
                'reason' => 'synced paid payment record to order',
            ];
        }

        try {
            $data = $this->paystack->verify($payment->reference);
        } catch (Throwable $exception) {
            Log::warning('Paystack payment reconciliation verify failed.', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'paystack_mode' => $this->paystack->mode(),
                'error' => $exception->getMessage(),
            ]);

            return ['reconciled' => false, 'reason' => 'verify failed: '.$exception->getMessage()];
        }

        if (($data['status'] ?? null) !== 'success') {
            return ['reconciled' => false, 'reason' => 'Paystack status: '.($data['status'] ?? 'unknown')];
        }

        return $this->applySuccessfulPaystackPayment($order, $payment, $data);
    }

    public function reconcileOrder(Order $order, bool $searchPaystack = true): bool
    {
        $payments = Payment::query()
            ->where('order_id', $order->id)
            ->where('provider', 'paystack')
            ->orderByDesc('id')
            ->get();

        foreach ($payments as $payment) {
            if ($this->reconcilePayment($payment)['reconciled']) {
                return true;
            }
        }

        if (! $searchPaystack || $order->payment_status === PaymentStatus::Paid) {
            return false;
        }

        return $this->reconcileOrderFromPaystackTransactions($order);
    }

    public function reconcileByReference(string $reference): bool
    {
        try {
            $data = $this->paystack->verify($reference);
        } catch (Throwable $exception) {
            Log::warning('Paystack reconcile by reference failed.', [
                'reference' => $reference,
                'paystack_mode' => $this->paystack->mode(),
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        if (($data['status'] ?? null) !== 'success') {
            return false;
        }

        $payment = $this->resolvePayment($reference, $data);

        if (! $payment) {
            return false;
        }

        return $this->reconcilePayment($payment)['reconciled'];
    }

    public function resolvePayment(string $reference, array $data = []): ?Payment
    {
        $payment = $this->findPaymentByReference($reference);

        if ($payment) {
            return $payment;
        }

        $payment = Payment::query()
            ->where('provider', 'paystack')
            ->where('metadata->initialization_reference', $reference)
            ->first();

        if ($payment) {
            $payment->update(['reference' => $reference]);

            return $payment->fresh();
        }

        $order = $this->findOrderFromPaystackData($data);

        if ($order) {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->where('provider', 'paystack')
                ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Failed])
                ->latest('id')
                ->first();

            if ($payment) {
                $payment->update(['reference' => $reference]);

                return $payment->fresh();
            }
        }

        return $this->recoverPaymentFromPaystackData($reference, $data);
    }

    /**
     * @return array{reconciled: bool, reason: string}
     */
    public function applySuccessfulPaystackPayment(Order $order, Payment $payment, array $data): array
    {
        $this->orderPayments->markAsPaid($order, $payment, $data);

        $order->refresh();

        if ($order->payment_status === PaymentStatus::Paid) {
            Log::info('Paystack payment synced to order.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
            ]);

            return ['reconciled' => true, 'reason' => 'verified with Paystack'];
        }

        Log::error('Paystack payment verified but order still unpaid.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
            'order_status' => $order->status->value,
            'payment_status' => $order->payment_status->value,
        ]);

        return ['reconciled' => false, 'reason' => 'mark as paid did not update order'];
    }

    /**
     * @return array<int, array{reference: string, status: string, amount: string}>
     */
    public function findSuccessfulPaystackTransactionsForOrder(Order $order, int $days = 30): array
    {
        $from = $order->created_at?->copy()->subDay() ?? now()->subDays($days);
        $matches = [];
        $page = 1;

        do {
            try {
                $transactions = $this->paystack->listTransactions([
                    'status' => 'success',
                    'from' => $from->toIso8601String(),
                    'to' => now()->toIso8601String(),
                    'perPage' => 100,
                    'page' => $page,
                ]);
            } catch (Throwable) {
                break;
            }

            foreach ($transactions as $transaction) {
                $orderId = $this->orderIdFromPaystackData($transaction);
                $orderNumber = $this->orderNumberFromPaystackData($transaction);

                $email = data_get($transaction, 'customer.email')
                    ?? data_get($transaction, 'authorization.email');
                $amount = isset($transaction['amount']) ? round((float) $transaction['amount'] / 100, 2) : null;

                $matchesOrder = (string) $orderId === (string) $order->id
                    || $orderNumber === $order->order_number
                    || (
                        $email
                        && $amount !== null
                        && round((float) $order->total, 2) === $amount
                        && in_array($email, array_filter([$order->billing_email, $order->shipping_email, $order->customerEmail()]), true)
                    );

                if (! $matchesOrder) {
                    continue;
                }

                $matches[] = [
                    'reference' => (string) ($transaction['reference'] ?? ''),
                    'status' => (string) ($transaction['status'] ?? 'unknown'),
                    'amount' => number_format($amount ?? 0, 2),
                ];
            }

            $page++;
        } while (count($transactions) === 100 && $page <= 10);

        return $matches;
    }

    protected function reconcileOrderFromPaystackTransactions(Order $order): bool
    {
        $from = $order->created_at?->copy()->subDay() ?? now()->subDays(30);
        $page = 1;

        do {
            try {
                $transactions = $this->paystack->listTransactions([
                    'status' => 'success',
                    'from' => $from->toIso8601String(),
                    'to' => now()->toIso8601String(),
                    'perPage' => 100,
                    'page' => $page,
                ]);
            } catch (Throwable $exception) {
                Log::warning('Paystack transaction list failed during reconciliation.', [
                    'order_id' => $order->id,
                    'error' => $exception->getMessage(),
                ]);

                return false;
            }

            foreach ($transactions as $transaction) {
                $orderId = $this->orderIdFromPaystackData($transaction);
                $orderNumber = $this->orderNumberFromPaystackData($transaction);

                if ((string) $orderId !== (string) $order->id && $orderNumber !== $order->order_number) {
                    continue;
                }

                $reference = (string) ($transaction['reference'] ?? '');

                if ($reference === '') {
                    continue;
                }

                $payment = $this->resolvePayment($reference, $transaction);

                if ($payment && $this->applySuccessfulPaystackPayment($order, $payment, $transaction)['reconciled']) {
                    return true;
                }
            }

            $page++;
        } while (count($transactions) === 100 && $page <= 5);

        return false;
    }

    public function findPaymentByReference(string $reference): ?Payment
    {
        return Payment::query()->where('reference', $reference)->first();
    }

    public function recoverPaymentFromPaystackData(string $reference, array $data): ?Payment
    {
        $existing = $this->findPaymentByReference($reference);

        if ($existing) {
            return $existing;
        }

        $order = $this->findOrderFromPaystackData($data);

        if (! $order) {
            Log::warning('Paystack payment could not be matched to an order.', [
                'reference' => $reference,
                'order_id' => $this->orderIdFromPaystackData($data),
                'order_number' => $this->orderNumberFromPaystackData($data),
                'customer_email' => data_get($data, 'customer.email'),
                'amount' => data_get($data, 'amount'),
            ]);

            return null;
        }

        return Payment::query()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'reference' => $reference,
            'provider' => 'paystack',
            'provider_transaction_id' => isset($data['id']) ? (string) $data['id'] : null,
            'amount' => isset($data['amount']) ? ((float) $data['amount'] / 100) : $order->total,
            'currency' => $data['currency'] ?? config('shop.currency'),
            'channel' => $data['channel'] ?? data_get($data, 'authorization.channel'),
            'status' => PaymentStatus::Pending,
            'metadata' => [
                'recovered' => true,
            ],
        ]);
    }

    public function findOrderFromPaystackData(array $data): ?Order
    {
        $orderId = $this->orderIdFromPaystackData($data);
        $orderNumber = $this->orderNumberFromPaystackData($data);

        if ($orderId) {
            $order = Order::query()->find($orderId);

            if ($order) {
                return $order;
            }
        }

        if ($orderNumber) {
            $order = Order::query()->where('order_number', $orderNumber)->first();

            if ($order) {
                return $order;
            }
        }

        $email = data_get($data, 'customer.email')
            ?? data_get($data, 'authorization.email');

        $amount = isset($data['amount']) ? round((float) $data['amount'] / 100, 2) : null;

        if (! $email || $amount === null) {
            return null;
        }

        return Order::query()
            ->where('payment_status', '!=', PaymentStatus::Paid)
            ->where(function ($query) use ($email) {
                $query->where('billing_email', $email)
                    ->orWhere('shipping_email', $email);
            })
            ->where('total', $amount)
            ->where('created_at', '>=', now()->subDays(14))
            ->latest('id')
            ->first();
    }

    public function orderIdFromPaystackData(array $data): ?int
    {
        $metadata = $this->normalizeMetadata($data['metadata'] ?? null);
        $orderId = $metadata['order_id'] ?? null;

        if ($orderId !== null && $orderId !== '') {
            return (int) $orderId;
        }

        foreach ($metadata['custom_fields'] ?? [] as $field) {
            if (($field['variable_name'] ?? null) === 'order_id' && filled($field['value'] ?? null)) {
                return (int) $field['value'];
            }
        }

        return null;
    }

    public function orderNumberFromPaystackData(array $data): ?string
    {
        $metadata = $this->normalizeMetadata($data['metadata'] ?? null);
        $orderNumber = $metadata['order_number'] ?? null;

        if (filled($orderNumber)) {
            return (string) $orderNumber;
        }

        foreach ($metadata['custom_fields'] ?? [] as $field) {
            if (($field['variable_name'] ?? null) === 'order_number' && filled($field['value'] ?? null)) {
                return (string) $field['value'];
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeMetadata(mixed $metadata): array
    {
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($metadata) ? $metadata : [];
    }
}
