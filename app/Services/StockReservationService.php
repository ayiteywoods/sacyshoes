<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockReservationService
{
    /**
     * Stock that can still be sold. On-hand quantity is the source of truth:
     * unpaid checkouts take units immediately, then return them after the hold window.
     */
    public function sellableQuantity(ProductVariant $variant, ?int $exceptOrderId = null): int
    {
        once(fn () => $this->releaseExpiredReservations());

        $quantity = ProductVariant::query()->whereKey($variant->id)->value('quantity');

        return max(0, (int) $quantity);
    }

    /**
     * @deprecated Cart reservations are no longer used. Use sellableQuantity().
     */
    public function availableQuantity(ProductVariant $variant, int $alreadyReserved = 0): int
    {
        return $this->sellableQuantity($variant);
    }

    /**
     * Take units out of inventory when checkout creates an unpaid order.
     *
     * @throws InsufficientStockException
     */
    public function reserveOrder(Order $order): void
    {
        $order->loadMissing('items');

        if ($order->stock_reserved) {
            return;
        }

        $this->fulfillOrderItems($order->items, allowPartial: false);

        $order->update(['stock_reserved' => true]);
    }

    /**
     * Put reserved units back when an unpaid order is cancelled or the hold expires.
     */
    public function releaseOrder(Order $order): bool
    {
        return (bool) DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();

            if (! $locked?->stock_reserved) {
                return false;
            }

            if ($locked->payment_status === PaymentStatus::Paid) {
                return false;
            }

            $locked->loadMissing('items');

            foreach ($locked->items as $item) {
                $qty = (int) $item->quantity;

                if ($qty <= 0) {
                    continue;
                }

                if ($item->product_variant_id) {
                    $variant = ProductVariant::query()
                        ->whereKey($item->product_variant_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $variant) {
                        continue;
                    }

                    $variant->update([
                        'quantity' => (int) $variant->quantity + $qty,
                    ]);

                    $this->syncProductQuantity($variant->product_id);

                    continue;
                }

                if ($item->product_id) {
                    Product::query()->whereKey($item->product_id)->increment('quantity', $qty);
                }
            }

            $locked->update(['stock_reserved' => false]);

            return true;
        });
    }

    /**
     * Make sure this unpaid order still has the items, or take them now if they are free.
     * Throws when another customer has already paid for the last unit.
     *
     * @throws InsufficientStockException
     */
    public function ensureOrderStockForPayment(Order $order): void
    {
        $order->loadMissing('items');

        if ($order->stock_reserved) {
            return;
        }

        $this->releaseExpiredReservations();

        $order->refresh()->load('items');

        if ($order->stock_reserved) {
            return;
        }

        $this->reserveOrder($order);
    }

    /**
     * Confirm that reserved units stay deducted after payment.
     * Late payments (after the hold was released) take remaining on-hand stock,
     * or fail if another customer already paid for those units.
     *
     * @throws InsufficientStockException
     */
    public function fulfillPaidOrder(Order $order, bool $allowPartial = false): bool
    {
        $order->loadMissing('items');

        if ($order->stock_reserved) {
            $order->update(['stock_reserved' => false]);

            return true;
        }

        return $this->fulfillOrderItems($order->items, $allowPartial);
    }

    public function releaseExpiredReservations(): int
    {
        $holdMinutes = max(1, (int) config('shop.stock_hold_minutes', 30));

        $orders = Order::query()
            ->where('stock_reserved', true)
            ->where('payment_status', PaymentStatus::Pending)
            ->where('status', OrderStatus::PendingPayment)
            ->where('created_at', '<', now()->subMinutes($holdMinutes))
            ->get();

        $released = 0;

        foreach ($orders as $order) {
            if ($this->releaseOrder($order)) {
                $released++;
            }
        }

        return $released;
    }

    /**
     * @param  Collection<int, OrderItem>  $items
     *
     * @throws InsufficientStockException
     */
    public function fulfillOrderItems(Collection $items, bool $allowPartial = false): bool
    {
        $fullyFulfilled = true;

        DB::transaction(function () use ($items, $allowPartial, &$fullyFulfilled) {
            $variants = [];
            $requiredQuantities = [];

            foreach ($items as $item) {
                if (! $item->product_variant_id) {
                    continue;
                }

                $variantId = (int) $item->product_variant_id;
                $requiredQuantities[$variantId] = ($requiredQuantities[$variantId] ?? 0) + (int) $item->quantity;
            }

            foreach ($requiredQuantities as $variantId => $required) {
                $locked = ProductVariant::query()
                    ->whereKey($variantId)
                    ->lockForUpdate()
                    ->first();

                if (! $locked) {
                    throw new InsufficientStockException('A product option on this order is no longer available.');
                }

                $take = min((int) $locked->quantity, $required);

                if ($take < $required) {
                    $fullyFulfilled = false;

                    if (! $allowPartial) {
                        throw new InsufficientStockException('An item in this order is no longer in stock.');
                    }
                }

                $variants[$variantId] = [$locked, $take];
            }

            foreach ($variants as [$variant, $take]) {
                if ($take <= 0) {
                    continue;
                }

                $variant->update([
                    'quantity' => (int) $variant->quantity - $take,
                    'reserved_quantity' => max(0, (int) $variant->reserved_quantity - $take),
                ]);

                $this->syncProductQuantity($variant->product_id);
            }

            foreach ($items as $item) {
                if ($item->product_variant_id || ! $item->product_id) {
                    continue;
                }

                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    continue;
                }

                $needed = (int) $item->quantity;
                $take = min((int) $product->quantity, $needed);

                if ($take < $needed) {
                    $fullyFulfilled = false;

                    if (! $allowPartial) {
                        throw new InsufficientStockException('An item in this order is no longer in stock.');
                    }
                }

                if ($take > 0) {
                    $product->decrement('quantity', $take);
                }
            }
        });

        return $fullyFulfilled;
    }

    public function syncProductQuantity(int $productId): void
    {
        Product::query()
            ->whereKey($productId)
            ->update([
                'quantity' => ProductVariant::query()
                    ->where('product_id', $productId)
                    ->sum('quantity'),
            ]);
    }

    /**
     * Clear legacy cart reservation counters left from the old cart-hold model.
     */
    public function clearLegacyReservations(): int
    {
        return ProductVariant::query()
            ->where('reserved_quantity', '>', 0)
            ->update(['reserved_quantity' => 0]);
    }
}
