<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\ShippingOption;
use App\Models\ShippingRegion;
use App\Services\AdminNotificationService;
use App\Services\CheckoutService;
use App\Services\CouponService;
use App\Services\OrderNotificationService;
use App\Support\GuestOrderAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkout,
        protected CouponService $coupons,
    ) {}

    public function create(): View
    {
        $cart = $this->checkout->getCartWithItems();
        $regions = ShippingRegion::query()
            ->with(['options' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $defaultRegionId = $regions->firstWhere('is_accra', true)?->id
            ?? $regions->first()?->id;

        $selectedRegionId = (int) old('shipping_region_id', $defaultRegionId);
        $selectedOptionId = old('shipping_option_id');

        $selectedRegion = $regions->firstWhere('id', $selectedRegionId);
        $deliveryFee = 0.0;

        if ($selectedRegion && ! $selectedRegion->is_accra && $selectedOptionId) {
            $option = ShippingOption::query()
                ->whereKey($selectedOptionId)
                ->where('shipping_region_id', $selectedRegionId)
                ->where('is_active', true)
                ->first();

            $deliveryFee = $option ? (float) $option->price : 0.0;
        }

        $appliedCoupon = $this->resolveAppliedCoupon($cart->items);
        $totals = $this->checkout->calculateTotals($cart->items, $deliveryFee, $appliedCoupon);
        $billing = old() ?: $this->checkout->defaultBillingData(auth()->user());

        return view('storefront.checkout.index', [
            'cart' => $cart,
            'items' => $cart->items,
            'totals' => $totals,
            'billing' => $billing,
            'regions' => $regions,
            'defaultRegionId' => $defaultRegionId,
            'appliedCoupon' => $appliedCoupon,
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        $cart = $this->checkout->getCartWithItems();
        $subtotal = (float) $cart->items->sum(fn ($item) => $item->lineTotal());

        $coupon = $this->coupons->resolveForCheckout($validated['coupon_code'], $subtotal);
        $this->coupons->applyToSession($coupon);

        return redirect()
            ->route('checkout.create')
            ->with('success', 'Coupon applied successfully.');
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->coupons->clearSession();

        return redirect()
            ->route('checkout.create')
            ->with('success', 'Coupon removed.');
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkout->placeOrder(
            auth()->user(),
            $request->only([
                'shipping_full_name',
                'shipping_phone',
                'shipping_email',
                'shipping_address',
                'shipping_city',
                'shipping_country',
                'shipping_region_id',
                'shipping_option_id',
                'customer_comment',
                'billing_full_name',
                'billing_phone',
                'billing_email',
                'billing_address',
                'billing_city',
                'billing_country',
            ]),
            $request->boolean('save_address')
        );

        GuestOrderAccess::remember($order);

        app(OrderNotificationService::class)->orderCreated($order);
        app(AdminNotificationService::class)->sync();

        return redirect()->to(GuestOrderAccess::paystackInitializeUrl($order));
    }

    public function success(Order $order): View|RedirectResponse
    {
        GuestOrderAccess::assertCanAccess($order);

        $order->load('items');

        return view('storefront.checkout.success', compact('order'));
    }

    private function resolveAppliedCoupon($items)
    {
        $coupon = $this->coupons->sessionCoupon();

        if (! $coupon) {
            return null;
        }

        $subtotal = (float) $items->sum(fn ($item) => $item->lineTotal());

        try {
            $this->coupons->validateForSubtotal($coupon, $subtotal);
        } catch (\Illuminate\Validation\ValidationException) {
            $this->coupons->clearSession();

            return null;
        }

        return $coupon;
    }
}
