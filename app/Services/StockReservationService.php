<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockReservationService
{
    public function availableQuantity(ProductVariant $variant, int $alreadyReserved = 0): int
    {
        $variant->refresh();

        return max(0, $variant->quantity - $variant->reserved_quantity + $alreadyReserved);
    }

    public function reserve(ProductVariant $variant, int $quantity, int $previouslyReserved = 0): void
    {
        if ($quantity <= 0) {
            return;
        }

        $available = $this->availableQuantity($variant, $previouslyReserved);

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => $available > 0
                    ? "Only {$available} item(s) available for the selected option."
                    : 'This product option is currently out of stock.',
            ]);
        }

        ProductVariant::query()
            ->whereKey($variant->id)
            ->increment('reserved_quantity', $quantity - $previouslyReserved);
    }

    public function release(ProductVariant $variant, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($variant, $quantity) {
            $locked = ProductVariant::query()
                ->whereKey($variant->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return;
            }

            $locked->update([
                'reserved_quantity' => max(0, $locked->reserved_quantity - $quantity),
            ]);
        });
    }

    public function fulfill(ProductVariant $variant, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($variant, $quantity) {
            $locked = ProductVariant::query()
                ->whereKey($variant->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return;
            }

            $locked->update([
                'quantity' => max(0, $locked->quantity - $quantity),
                'reserved_quantity' => max(0, $locked->reserved_quantity - $quantity),
            ]);

            $this->syncProductQuantity($locked->product_id);
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

    public function reservationExpiry(): Carbon
    {
        return now()->addMinutes((int) config('shop.cart_reservation_minutes', 60));
    }
}
