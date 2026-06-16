<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;

class AdminNotificationService
{
    /**
     * @return Collection<int, AdminNotification>
     */
    public function get(): Collection
    {
        $this->sync();

        return AdminNotification::query()
            ->whereNull('dismissed_at')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function markAsRead(AdminNotification $notification): void
    {
        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }
    }

    public function dismiss(AdminNotification $notification): void
    {
        $notification->update(['dismissed_at' => now()]);
    }

    public function dismissAll(): void
    {
        AdminNotification::query()
            ->whereNull('dismissed_at')
            ->update(['dismissed_at' => now()]);
    }

    public function sync(): void
    {
        $activeKeys = collect();

        Order::query()
            ->where('payment_status', PaymentStatus::Pending)
            ->where('status', OrderStatus::PendingPayment)
            ->latest()
            ->limit(10)
            ->get()
            ->each(function (Order $order) use ($activeKeys) {
                $key = "payment:order:{$order->id}";
                $activeKeys->push($key);

                AdminNotification::query()->updateOrCreate(
                    ['reference_key' => $key],
                    [
                        'type' => 'payment',
                        'title' => 'Payment pending',
                        'message' => "Order {$order->order_number} is awaiting payment.",
                        'url' => route('admin.orders.show', $order),
                    ]
                );
            });

        Order::query()
            ->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing])
            ->latest()
            ->limit(10)
            ->get()
            ->each(function (Order $order) use ($activeKeys) {
                $key = "attention:order:{$order->id}";
                $activeKeys->push($key);

                AdminNotification::query()->updateOrCreate(
                    ['reference_key' => $key],
                    [
                        'type' => 'order',
                        'title' => 'Order needs attention',
                        'message' => "Order {$order->order_number} is {$order->status->label()}.",
                        'url' => route('admin.orders.show', $order),
                    ]
                );
            });

        Product::query()
            ->where('status', ProductStatus::Active)
            ->where('quantity', '<', 10)
            ->orderBy('quantity')
            ->limit(10)
            ->get()
            ->each(function (Product $product) use ($activeKeys) {
                $key = "stock:product:{$product->id}";
                $activeKeys->push($key);

                AdminNotification::query()->updateOrCreate(
                    ['reference_key' => $key],
                    [
                        'type' => 'stock',
                        'title' => 'Low stock alert',
                        'message' => "{$product->name} has only {$product->quantity} left in stock.",
                        'url' => route('admin.products.edit', $product),
                    ]
                );
            });

        $staleQuery = AdminNotification::query()
            ->whereIn('type', ['payment', 'order', 'stock']);

        if ($activeKeys->isNotEmpty()) {
            $staleQuery->whereNotIn('reference_key', $activeKeys->all());
        }

        $staleQuery->delete();
    }
}
