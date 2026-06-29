<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockReservationService
{
    /**
     * Physical stock available for sale. Cart items do not reduce this until payment succeeds.
     */
    public function sellableQuantity(ProductVariant $variant): int
    {
        $variant->refresh();

        return max(0, (int) $variant->quantity);
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
    public function fulfillOrderItems(Collection $items): void
    {
        DB::transaction(function () use ($items) {
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

                if ($locked->quantity < $required) {
                    throw new InsufficientStockException('An item in this order is no longer in stock.');
                }

                $variants[$variantId] = $locked;
            }

            foreach ($items as $item) {
                if (! $item->product_variant_id) {
                    if ($item->product_id) {
                        Product::query()
                            ->whereKey($item->product_id)
                            ->where('quantity', '>=', $item->quantity)
                            ->decrement('quantity', $item->quantity);
                    }

                    continue;
                }

                $variantId = (int) $item->product_variant_id;
                $needed = (int) $item->quantity;
                $variant = $variants[$variantId];

                $variant->update([
                    'quantity' => $variant->quantity - $needed,
                    'reserved_quantity' => max(0, (int) $variant->reserved_quantity - $needed),
                ]);

                $variants[$variantId] = $variant->fresh();
            }

            foreach ($variants as $variant) {
                $this->syncProductQuantity($variant->product_id);
            }
        });
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
