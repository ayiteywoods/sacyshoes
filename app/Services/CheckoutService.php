<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ShippingOption;
use App\Models\ShippingRegion;
use App\Models\User;
use App\Support\OrderNumberGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CartService $cart,
        protected CouponService $coupons,
    ) {}

    public function getCartWithItems(): Cart
    {
        return $this->cart->resolve()->load(['items.product', 'items.variant']);
    }

    /**
     * @return array{subtotal: float, delivery_fee: float, tax: float, discount: float, total: float}
     */
    public function calculateTotals(Collection $items, ?float $deliveryFee = null, ?Coupon $coupon = null): array
    {
        $subtotal = (float) $items->sum(fn ($item) => $item->lineTotal());
        $deliveryFee = $deliveryFee ?? 0.0;
        $discount = 0.0;

        if ($coupon) {
            $discount = app(CouponService::class)->discountAmount($coupon, $subtotal);
        }

        $taxableSubtotal = max(0, $subtotal - $discount);
        $tax = round($taxableSubtotal * (float) config('shop.tax_rate'), 2);
        $total = round($taxableSubtotal + $deliveryFee + $tax, 2);

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
        ];
    }

    public function defaultBillingData(?User $user): array
    {
        if (! $user) {
            return [
                'billing_full_name' => '',
                'billing_phone' => '',
                'billing_email' => '',
                'billing_address' => '',
                'billing_city' => '',
                'billing_country' => config('shop.default_country'),
            ];
        }

        $address = $user->addresses()->where('is_default', true)->first()
            ?? $user->addresses()->latest()->first();

        return [
            'billing_full_name' => $address?->full_name ?? trim("{$user->first_name} {$user->last_name}") ?: $user->name,
            'billing_phone' => $address?->phone ?? $user->phone ?? '',
            'billing_email' => $address?->email ?? $user->email,
            'billing_address' => $address?->address_line ?? '',
            'billing_city' => $address?->city ?? '',
            'billing_country' => $address?->country ?? config('shop.default_country'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function placeOrder(?User $user, array $data, bool $saveAddress = false): Order
    {
        $cart = $this->getCartWithItems();

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        foreach ($cart->items as $item) {
            $product = $item->product->fresh();
            $variant = $item->variant?->fresh();

            if (! $variant) {
                throw ValidationException::withMessages([
                    'cart' => "{$product->name} is missing a size or color. Please update your cart.",
                ]);
            }

            if (! $product->isActive() || ! $variant->is_active) {
                throw ValidationException::withMessages([
                    'cart' => "{$product->name} ({$variant->displayLabel()}) is no longer available.",
                ]);
            }

            $available = app(StockReservationService::class)
                ->availableQuantity($variant, $item->quantity);

            if ($item->quantity > $available) {
                throw ValidationException::withMessages([
                    'cart' => "Only {$available} of {$product->name} ({$variant->displayLabel()}) available in stock.",
                ]);
            }
        }

        $deliveryFee = $this->resolveDeliveryFee(
            (int) ($data['shipping_region_id'] ?? 0),
            $data['shipping_option_id'] ?? null,
        );

        $coupon = $this->resolveCheckoutCoupon($cart->items);
        $totals = $this->calculateTotals($cart->items, $deliveryFee, $coupon);

        return DB::transaction(function () use ($user, $data, $saveAddress, $cart, $totals, $deliveryFee, $coupon) {
            $region = isset($data['shipping_region_id'])
                ? ShippingRegion::query()->find($data['shipping_region_id'])
                : null;
            $option = isset($data['shipping_option_id'])
                ? ShippingOption::query()->find($data['shipping_option_id'])
                : null;

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user?->id,
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $totals['delivery_fee'],
                'shipping_fee' => $deliveryFee,
                'tax' => $totals['tax'],
                'discount_amount' => $totals['discount'],
                'total' => $totals['total'],
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'payment_method' => 'paystack',
                'payment_status' => PaymentStatus::Pending,
                'status' => OrderStatus::PendingPayment,
                'billing_full_name' => $data['billing_full_name'],
                'billing_phone' => $data['billing_phone'],
                'billing_email' => $data['billing_email'],
                'billing_address' => $data['billing_address'],
                'billing_city' => $data['billing_city'],
                'billing_country' => $data['billing_country'],
                'shipping_full_name' => $data['shipping_full_name'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_email' => $data['shipping_email'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'],
                'shipping_country' => $data['shipping_country'],
                'shipping_region_id' => $region?->id,
                'shipping_option_id' => $option?->id,
                'shipping_region_name' => $region?->name,
                'shipping_option_name' => $option?->name,
                'customer_comment' => filled($data['customer_comment'] ?? null) ? $data['customer_comment'] : null,
                'payment_due_at' => now()->addHours((int) config('shop.order_payment_timeout_hours', 24)),
            ]);

            foreach ($cart->items as $item) {
                $variant = $item->variant;

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'variant_sku' => $variant?->sku,
                    'variant_options' => $variant?->optionSnapshot(),
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->lineTotal(),
                ]);
            }

            if ($saveAddress && $user) {
                $this->saveAddress($user, $data);
            }

            if ($coupon) {
                $this->coupons->incrementUsage($coupon);
                $this->coupons->clearSession();
            }

            $this->cart->clear(releaseStock: false);

            return $order->load('items');
        });
    }

    private function resolveCheckoutCoupon(Collection $items): ?Coupon
    {
        $coupon = $this->coupons->sessionCoupon();

        if (! $coupon) {
            return null;
        }

        $subtotal = (float) $items->sum(fn ($item) => $item->lineTotal());

        try {
            $this->coupons->validateForSubtotal($coupon, $subtotal);
        } catch (ValidationException) {
            $this->coupons->clearSession();

            return null;
        }

        return $coupon;
    }

    private function resolveDeliveryFee(int $regionId, int|string|null $optionId): float
    {
        $region = ShippingRegion::query()
            ->whereKey($regionId)
            ->where('is_active', true)
            ->first();

        if (! $region) {
            return 0.0;
        }

        // Accra: customer pays rider directly.
        if ($region->is_accra) {
            return 0.0;
        }

        if (! $optionId) {
            throw ValidationException::withMessages([
                'shipping_option_id' => 'Please select a delivery option for your region.',
            ]);
        }

        $option = ShippingOption::query()
            ->whereKey($optionId)
            ->where('shipping_region_id', $regionId)
            ->where('is_active', true)
            ->first();

        if (! $option) {
            throw ValidationException::withMessages([
                'shipping_option_id' => 'Selected delivery option is not available for the chosen region.',
            ]);
        }

        return (float) $option->price;
    }

    protected function saveAddress(User $user, array $billing): void
    {
        $user->addresses()->update(['is_default' => false]);

        $user->addresses()->updateOrCreate(
            [
                'address_line' => $billing['billing_address'],
                'city' => $billing['billing_city'],
            ],
            [
                'full_name' => $billing['billing_full_name'],
                'phone' => $billing['billing_phone'],
                'email' => $billing['billing_email'],
                'country' => $billing['billing_country'],
                'is_default' => true,
            ]
        );
    }

    protected function generateOrderNumber(): string
    {
        return OrderNumberGenerator::next();
    }
}
