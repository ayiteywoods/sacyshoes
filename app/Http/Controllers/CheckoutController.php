<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkout
    ) {}

    public function create(): View
    {
        $cart = $this->checkout->getCartWithItems();
        $totals = $this->checkout->calculateTotals($cart->items);
        $billing = old() ?: $this->checkout->defaultBillingData(auth()->user());

        return view('storefront.checkout.index', [
            'cart' => $cart,
            'items' => $cart->items,
            'totals' => $totals,
            'billing' => $billing,
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkout->placeOrder(
            auth()->user(),
            $request->only([
                'billing_full_name',
                'billing_phone',
                'billing_email',
                'billing_address',
                'billing_city',
                'billing_country',
            ]),
            $request->boolean('save_address')
        );

        app(\App\Services\OrderNotificationService::class)->orderCreated($order);
        app(\App\Services\AdminNotificationService::class)->sync();

        return redirect()
            ->route('checkout.success', $order)
            ->with('success', 'Your order has been placed. Complete payment to confirm it.');
    }

    public function success(Order $order): View|RedirectResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items');

        return view('storefront.checkout.success', compact('order'));
    }
}
