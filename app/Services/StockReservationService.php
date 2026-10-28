<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockReservationService
{
    /**
     * Stock that can still be sold: on-hand quantity minus unpaid pending orders.
     */
    public function sellableQuantity(ProductVariant $variant, ?int $exceptOrderId = null): int
    {
        return max(0, (int) $variant->quantity - $this->pendingOrderQuantity((int) $variant->id, $exceptOrderId));
    }

    /**
     * @deprecated Cart reservations are no longer used. Use sellableQuantity().
     */
    public function availableQuantity(ProductVariant $variant, int $alreadyReserved = 0): int
    {
        return $this->sellableQuantity($variant);
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

    public function pendingOrderQuantity(int $variantId, ?int $exceptOrderId = null): int
    {
        return (int) $this->pendingOrderItemsQuery($exceptOrderId)
            ->where('order_items.product_variant_id', $variantId)
            ->sum('order_items.quantity');
    }

    /**
     * @return Builder<OrderItem>
     */
    protected function pendingOrderItemsQuery(?int $exceptOrderId = null): Builder
    {
        $holdMinutes = max(1, (int) config('shop.stock_hold_minutes', 30));

        return OrderItem::query()
            ->whereHas('order', function ($orderQuery) use ($exceptOrderId, $holdMinutes) {
                $orderQuery
                    ->where('payment_status', PaymentStatus::Pending)
                    ->where('status', OrderStatus::PendingPayment)
                    ->where('created_at', '>=', now()->subMinutes($holdMinutes));

                if ($exceptOrderId) {
                    $orderQuery->where('id', '!=', $exceptOrderId);
                }
            });
    }
}
