<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        protected StockReservationService $reservations
    ) {}

    public function resolve(): Cart
    {
        if (auth()->check()) {
            return Cart::query()->firstOrCreate([
                'user_id' => auth()->id(),
            ]);
        }

        return Cart::query()->firstOrCreate([
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
                $existing = $userCart->items()
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->first();

                if ($existing) {
                    $previousQuantity = $existing->quantity;
                    $newQuantity = $previousQuantity + $guestItem->quantity;

                    $this->reservations->reserve(
                        $guestItem->variant,
                        $newQuantity,
                        $previousQuantity
                    );

                    $existing->update([
                        'quantity' => $newQuantity,
                        'unit_price' => $guestItem->unit_price,
                        'reserved_until' => $this->reservations->reservationExpiry(),
                    ]);

                    $this->reservations->release($guestItem->variant, $guestItem->quantity);
                } else {
                    $userCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity,
                        'unit_price' => $guestItem->unit_price,
                        'reserved_until' => $guestItem->reserved_until ?? $this->reservations->reservationExpiry(),
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
            $previousQuantity = $item->quantity;
            $newQuantity = $previousQuantity + $quantity;
            $this->assertVariantAvailable($product, $variant, $newQuantity, $previousQuantity);

            $this->reservations->reserve($variant, $newQuantity, $previousQuantity);

            $item->update([
                'quantity' => $newQuantity,
                'unit_price' => $variant->sellingPrice(),
                'reserved_until' => $this->reservations->reservationExpiry(),
            ]);

            return $item->fresh();
        }

        $this->reservations->reserve($variant, $quantity);

        return $cart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit_price' => $variant->sellingPrice(),
            'reserved_until' => $this->reservations->reservationExpiry(),
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $this->assertItemBelongsToCurrentCart($item);
        $item->loadMissing(['product', 'variant']);

        $previousQuantity = $item->quantity;
        $this->assertVariantAvailable($item->product, $item->variant, $quantity, $previousQuantity);

        $this->reservations->reserve($item->variant, $quantity, $previousQuantity);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $item->variant->sellingPrice(),
            'reserved_until' => $this->reservations->reservationExpiry(),
        ]);

        return $item->fresh();
    }

    public function remove(CartItem $item): void
    {
        $this->assertItemBelongsToCurrentCart($item);
        $item->loadMissing('variant');

        if ($item->variant) {
            $this->reservations->release($item->variant, $item->quantity);
        }

        $item->delete();
    }

    public function clear(bool $releaseStock = true): void
    {
        $cart = $this->resolve()->load('items.variant');

        if ($releaseStock) {
            foreach ($cart->items as $item) {
                if ($item->variant) {
                    $this->reservations->release($item->variant, $item->quantity);
                }
            }
        }

        $cart->items()->delete();
    }

    public function count(): int
    {
        return $this->resolve()->itemCount();
    }

    protected function assertItemBelongsToCurrentCart(CartItem $item): void
    {
        if ($item->cart_id !== $this->resolve()->id) {
            abort(403);
        }
    }

    protected function assertVariantAvailable(
        Product $product,
        ProductVariant $variant,
        int $quantity,
        int $alreadyReserved = 0
    ): void {
        if ($variant->product_id !== $product->id) {
            throw ValidationException::withMessages([
                'product_variant_id' => 'The selected option does not belong to this product.',
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

        $available = $this->reservations->availableQuantity($variant, $alreadyReserved);

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
