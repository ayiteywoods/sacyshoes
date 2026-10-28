<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderPaymentService
{
    public function __construct(
        protected OrderNotificationService $notifications,
        protected StockReservationService $stock,
        protected PaystackService $paystack,
    ) {}

    public function markAsPaid(Order $order, Payment $payment, array $data = []): void
    {
        $wasAlreadyPaid = false;
        $stockUnavailable = false;
        $wasCancelled = false;

        DB::transaction(function () use ($order, $payment, $data, &$wasAlreadyPaid, &$stockUnavailable, &$wasCancelled) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($order->payment_status === PaymentStatus::Paid) {
                $wasAlreadyPaid = true;

                return;
            }

            if ($order->payment_status === PaymentStatus::Refunded || $order->status === OrderStatus::Refunded) {
                return;
            }

            if ($order->payment_status === PaymentStatus::Failed) {
                Log::info('Reconciling order previously marked failed after Paystack success.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);
            }

            $wasCancelled = $order->status === OrderStatus::Cancelled;

            $order->loadMissing('items');

            try {
                $this->stock->fulfillPaidOrder($order, allowPartial: false);
            } catch (InsufficientStockException $exception) {
                $stockUnavailable = true;

                $this->rejectSoldOutOrder($order, $payment, $data, $exception->getMessage());

                Log::warning('Payment blocked because this item was already sold.', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'message' => $exception->getMessage(),
                ]);

                return;
            }

            $paidAt = isset($data['paid_at'])
                ? Carbon::parse($data['paid_at'])
                : now();

            $transactionId = isset($data['id']) ? (string) $data['id'] : $payment->provider_transaction_id;
            $receiptNumber = filled($data['receipt_number'] ?? null)
                ? (string) $data['receipt_number']
                : $transactionId;
            $displayReference = $receiptNumber
                ? $order->order_number.'-'.$receiptNumber
                : null;

            $paymentMetadata = array_merge($payment->metadata ?? [], [
                'verification' => $data,
                'display_reference' => $displayReference,
            ]);

            // Avoid observers/mail during the payment transaction so a notification
            // failure cannot roll back a successful Paystack charge.
            Order::withoutEvents(function () use ($order, $payment, $data, $paidAt, $transactionId, $paymentMetadata) {
                $payment->update([
                    'status' => PaymentStatus::Paid,
                    'channel' => $data['channel'] ?? data_get($data, 'authorization.channel') ?? $payment->channel,
                    'provider_transaction_id' => $transactionId ?: $payment->provider_transaction_id,
                    'paid_at' => $paidAt,
                    'metadata' => $paymentMetadata,
                ]);

                $order->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => OrderStatus::Paid,
                    'paid_at' => $paidAt,
                ]);
            });

            if ($wasCancelled) {
                Log::info('Reinstated cancelled order after confirmed payment.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_id' => $payment->id,
                ]);
            }
        });

        if ($wasAlreadyPaid) {
            return;
        }

        $order->refresh();
        $payment->refresh();

        if ($stockUnavailable) {
            $this->refundSoldOutPayment($payment);

            return;
        }

        if ($order->payment_status !== PaymentStatus::Paid) {
            Log::error('markAsPaid finished but order is still unpaid.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_id' => $payment->id,
            ]);

            return;
        }

        try {
            app(CartService::class)->clearOrderItems($order);
        } catch (Throwable $exception) {
            Log::warning('Failed clearing cart after payment.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            $this->notifications->paymentReceived($order);
        } catch (Throwable $exception) {
            Log::error('Payment email failed after order was marked paid.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            app(AdminNotificationService::class)->sync();
        } catch (Throwable $exception) {
            Log::warning('Admin notification sync failed after payment.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function rejectSoldOutOrder(Order $order, Payment $payment, array $data, string $message): void
    {
        $paymentMetadata = array_merge($payment->metadata ?? [], [
            'verification' => $data,
            'stock_unavailable' => true,
            'stock_unavailable_message' => 'This item was already sold to another customer. Payment did not go through.',
            'stock_unavailable_detail' => $message,
        ]);

        Order::withoutEvents(function () use ($order, $payment, $data, $paymentMetadata) {
            $payment->update([
                'status' => PaymentStatus::Refunded,
                'channel' => $data['channel'] ?? data_get($data, 'authorization.channel') ?? $payment->channel,
                'metadata' => $paymentMetadata,
            ]);

            $order->update([
                'payment_status' => PaymentStatus::Refunded,
                'status' => OrderStatus::Cancelled,
                'stock_reserved' => false,
            ]);
        });
    }

    protected function refundSoldOutPayment(Payment $payment): void
    {
        if ($payment->provider !== 'paystack' || blank($payment->reference)) {
            return;
        }

        try {
            $refund = $this->paystack->refund(
                (string) $payment->reference,
                'Item already sold to another customer.',
            );

            $payment->update([
                'metadata' => array_merge($payment->metadata ?? [], [
                    'stock_unavailable_refund' => $refund,
                    'stock_unavailable_refunded_at' => now()->toIso8601String(),
                ]),
            ]);
        } catch (Throwable $exception) {
            Log::error('Paystack refund failed after sold-out payment was blocked.', [
                'payment_id' => $payment->id,
                'reference' => $payment->reference,
                'error' => $exception->getMessage(),
            ]);

            $payment->update([
                'metadata' => array_merge($payment->metadata ?? [], [
                    'stock_unavailable_refund_failed' => true,
                    'stock_unavailable_refund_error' => $exception->getMessage(),
                ]),
            ]);
        }
    }
}
