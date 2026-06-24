<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\OrderCancellationService;
use Illuminate\Console\Command;

class CancelUnpaidOrders extends Command
{
    protected $signature = 'orders:cancel-unpaid';

    protected $description = 'Cancel unpaid orders past their payment deadline and release reserved stock';

    public function handle(OrderCancellationService $cancellations): int
    {
        $orders = Order::query()
            ->where('status', OrderStatus::PendingPayment)
            ->where('payment_status', PaymentStatus::Pending)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No unpaid orders to cancel.');

            return self::SUCCESS;
        }

        $cancelled = 0;

        foreach ($orders as $order) {
            if ($cancellations->cancelUnpaid($order)) {
                $cancelled++;
            }
        }

        $this->info("Cancelled {$cancelled} unpaid order(s).");

        return self::SUCCESS;
    }
}
