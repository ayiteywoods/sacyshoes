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

class OrderPaymentService
{
    public function __construct(
        protected OrderNotificationService $notifications,
        protected StockReservationService $stock
    ) {}

    public function markAsPaid(Order $order, Payment $payment, array $data = []): void
    {
        $wasAlreadyPaid = false;
        $stockUnavailable = false;

        DB::transaction(function () use ($order, $payment, $data, &$wasAlreadyPaid, &$stockUnavailable) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($order->status === OrderStatus::Cancelled) {
                return;
            }

            if ($order->payment_status === PaymentStatus::Paid) {
                $wasAlreadyPaid = true;

                return;
            }

            $order->loadMissing('items');

            try {
                $this->stock->fulfillOrderItems($order->items);
            } catch (InsufficientStockException $exception) {
                $stockUnavailable = true;

                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'failure_reason' => 'stock_unavailable',
                        'failure_message' => $exception->getMessage(),
                        'verification' => $data,
                    ]),
                ]);

                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'payment_status' => PaymentStatus::Failed,
                ]);

                Log::warning('Payment received but stock unavailable.', [
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

            $payment->update([
                'status' => PaymentStatus::Paid,
                'channel' => $data['channel'] ?? data_get($data, 'authorization.channel') ?? $payment->channel,
                'provider_transaction_id' => $transactionId ?: $payment->provider_transaction_id,
                'paid_at' => $paidAt,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'verification' => $data,
                    'display_reference' => $displayReference,
                ]),
            ]);

            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Paid,
                'paid_at' => $paidAt,
            ]);
        });

        if ($wasAlreadyPaid || $stockUnavailable) {
            if ($stockUnavailable) {
                app(AdminNotificationService::class)->sync();
            }

            return;
        }

        $order->refresh();
        $payment->refresh();

        $this->notifications->paymentReceived($order);
        app(AdminNotificationService::class)->sync();
    }
}
