<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
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
            ->with('items')
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
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'quantity' => $existing->quantity + $guestItem->quantity,
                        'unit_price' => $guestItem->unit_price,
                    ]);
                } else {
                    $userCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'quantity' => $guestItem->quantity,
                        'unit_price' => $guestItem->unit_price,
                    ]);
                }
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    public function add(Product $product, int $quantity = 1): CartItem
    {
        $this->assertProductAvailable($product, $quantity);

        $cart = $this->resolve()->load('items');
        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $newQuantity = $item->quantity + $quantity;
            $this->assertProductAvailable($product, $newQuantity);

            $item->update([
                'quantity' => $newQuantity,
                'unit_price' => $product->sellingPrice(),
            ]);

            return $item->fresh();
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->sellingPrice(),
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $this->assertItemBelongsToCurrentCart($item);
        $this->assertProductAvailable($item->product, $quantity);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => $item->product->sellingPrice(),
        ]);

        return $item->fresh();
    }

    public function remove(CartItem $item): void
    {
        $this->assertItemBelongsToCurrentCart($item);
        $item->delete();
    }

    public function clear(): void
    {
        $this->resolve()->items()->delete();
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

    protected function assertProductAvailable(Product $product, int $quantity): void
    {
        if (! $product->isInStock()) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is currently out of stock.',
            ]);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        if ($quantity > $product->quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$product->quantity} item(s) available in stock.",
            ]);
        }
    }
}
