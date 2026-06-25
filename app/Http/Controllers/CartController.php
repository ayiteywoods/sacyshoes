<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ProductVariantResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected CheckoutService $checkout,
        protected ProductVariantResolver $variantResolver
    ) {}

    public function index(): View
    {
        $cart = $this->cart->resolve()->load(['items.product.images', 'items.product.category', 'items.variant']);
        $totals = $this->checkout->calculateTotals($cart->items);

        return view('storefront.cart.index', [
            'cart' => $cart,
            'items' => $cart->items,
            'subtotal' => $totals['subtotal'],
            'totals' => $totals,
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));

        abort_unless($product->isVisibleOnStorefront(), 404);

        $variant = $this->variantResolver->resolveForProduct(
            $product,
            $request->string('variant_size')->toString(),
            $request->string('variant_color')->toString(),
            $request->filled('variant_heel') ? $request->string('variant_heel')->toString() : null,
        );

        $this->cart->add($product, $variant, $request->integer('quantity', 1));

        return redirect()
            ->route('cart.index')
            ->with('success', "{$product->name} added to your cart.");
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->cart->updateQuantity($cartItem, $request->integer('quantity'));

        return redirect()
            ->route('cart.index')
            ->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->cart->remove($cartItem);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Item removed from your cart.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Your cart has been cleared.');
    }
}
