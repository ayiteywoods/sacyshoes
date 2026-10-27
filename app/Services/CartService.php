<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected ?Cart $resolvedCart = null;

    public function __construct(
        protected StockReservationService $stock
    ) {}

    public function resolve(): Cart
    {
        if ($this->resolvedCart) {
            return $this->resolvedCart;
        }

        if (auth()->check()) {
            return $this->resolvedCart = Cart::query()->firstOrCreate([
                'user_id' => auth()->id(),
            ]);
        }

        return $this->resolvedCart = Cart::query()->firstOrCreate([
            'session_id' => session()->getId(),
        ]);
    }

    public function mergeGuestCartIntoUser(User $user): void
    {
        $guestCart = Cart::query()
            ->with('items.variant')
            ->where('session_id', session()->getId())
            ->whereNull('user_id')
            ->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::query()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $guestItem) {
                $available = $guestItem->variant
                    ? $this->stock->sellableQuantity($guestItem->variant)
                    : 0;

                if ($available <= 0) {
                    continue;
                }

                $existing = $userCart->items()
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'quantity' => min($available, $existing->quantity + $guestItem->quantity),
                        'unit_price' => $guestItem->unit_price,
                    ]);
                } else {
                    $userCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => min($available, $guestItem->quantity),
                        'unit_price' => $guestItem->unit_price,
                    ]);
                }
            }

            foreach ($guestCart->items as $guestItem) {
                $guestItem->delete();
            }

            $guestCart->delete();
        });
    }

    public function add(Product $product, ProductVariant $variant, int $quantity = 1): CartItem
    {
        $this->assertVariantAvailable($product, $variant, $quantity);

        $cart = $this->resolve()->load('items');
        $item = $cart->items()->where('product_variant_id', $variant->id)->first();

        if ($item) {
            $newQuantity = $item->quantity + $quantity;
            $this->assertVariantAvailable($product, $variant, $newQuantity);

            $item->update([
                'quantity' => $newQuantity,
                'unit_price' => $variant->sellingPrice(),
            ]);

            return $item->fresh();
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit_price' => $variant->sellingPrice(),
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $this->assertItemBelongsToCurrentCart($item);
        $item->loadMissing(['product', 'variant']);

        $this->assertVariantAvailable($item->product, $item->variant, $quantity);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $item->variant->sellingPrice(),
        ]);

        return $item->fresh();
    }

    public function remove(CartItem $item): void
    {
        $this->assertItemBelongsToCurrentCart($item);
        $item->delete();
    }

    public function clear(bool $releaseStock = true): void
    {
        $this->resolve()->items()->delete();
    }

    public function clearOrderItems(Order $order): void
    {
        $variantIds = $order->items()
            ->whereNotNull('product_variant_id')
            ->pluck('product_variant_id');

        if ($variantIds->isEmpty()) {
            return;
        }

        $this->resolve()
            ->items()
            ->whereIn('product_variant_id', $variantIds)
            ->delete();
    }

    /**
     * @return array<int, array{item: CartItem, available: int, is_out_of_stock: bool, exceeds_stock: bool}>
     */
    public function itemsWithAvailability(): array
    {
        $cart = $this->resolve()->load([
            'items.product.images',
            'items.product.category',
            'items.variant',
        ]);

        return $cart->items->map(function (CartItem $item) {
            $available = $item->variant
                ? $this->stock->sellableQuantity($item->variant)
                : max(0, (int) ($item->product?->quantity ?? 0));

            return [
                'item' => $item,
                'available' => $available,
                'is_out_of_stock' => $available <= 0,
                'exceeds_stock' => $item->quantity > $available,
            ];
        })->all();
    }

    public function hasUnavailableItems(): bool
    {
        foreach ($this->itemsWithAvailability() as $row) {
            if ($row['is_out_of_stock'] || $row['exceeds_stock']) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return $this->resolve()->itemCount();
    }

    protected function assertItemBelongsToCurrentCart(CartItem $item): void
    {
        if ((int) $item->cart_id !== (int) $this->resolve()->id) {
            abort(403);
        }
    }

    protected function assertVariantAvailable(
        Product $product,
        ProductVariant $variant,
        int $quantity,
    ): void {
        if ((int) $variant->product_id !== (int) $product->id) {
            throw ValidationException::withMessages([
                'variant_size' => 'The selected option does not belong to this product.',
            ]);
        }

        if (! $product->isActive()) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is currently unavailable.',
            ]);
        }

        if (! $variant->is_active) {
            throw ValidationException::withMessages([
                'quantity' => 'This product option is currently unavailable.',
            ]);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        $available = $this->stock->sellableQuantity($variant);

        if ($available <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'This product option is currently out of stock.',
            ]);
        }

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$available} item(s) available for the selected option.",
            ]);
        }
    }
}
