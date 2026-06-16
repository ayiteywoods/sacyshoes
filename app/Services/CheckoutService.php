<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected CartService $cart
    ) {}

    public function getCartWithItems(): Cart
    {
        return $this->cart->resolve()->load(['items.product']);
    }

    /**
     * @return array{subtotal: float, delivery_fee: float, tax: float, total: float}
     */
    public function calculateTotals(Collection $items): array
    {
        $subtotal = (float) $items->sum(fn ($item) => $item->lineTotal());
        $deliveryFee = $subtotal >= config('shop.free_delivery_threshold')
            ? 0.0
            : (float) config('shop.delivery_fee');
        $tax = round($subtotal * (float) config('shop.tax_rate'), 2);
        $total = round($subtotal + $deliveryFee + $tax, 2);

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    public function defaultBillingData(User $user): array
    {
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

    public function placeOrder(User $user, array $billing, bool $saveAddress = false): Order
    {
        $cart = $this->getCartWithItems();

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        foreach ($cart->items as $item) {
            $product = $item->product->fresh();

            if (! $product->isInStock()) {
                throw ValidationException::withMessages([
                    'cart' => "{$product->name} is no longer available.",
                ]);
            }

            if ($item->quantity > $product->quantity) {
                throw ValidationException::withMessages([
                    'cart' => "Only {$product->quantity} of {$product->name} available in stock.",
                ]);
            }
        }

        $totals = $this->calculateTotals($cart->items);

        return DB::transaction(function () use ($user, $billing, $saveAddress, $cart, $totals) {
            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $totals['delivery_fee'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'payment_method' => 'paystack',
                'payment_status' => PaymentStatus::Pending,
                'status' => OrderStatus::PendingPayment,
                'billing_full_name' => $billing['billing_full_name'],
                'billing_phone' => $billing['billing_phone'],
                'billing_email' => $billing['billing_email'],
                'billing_address' => $billing['billing_address'],
                'billing_city' => $billing['billing_city'],
                'billing_country' => $billing['billing_country'],
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->lineTotal(),
                ]);
            }

            if ($saveAddress) {
                $this->saveAddress($user, $billing);
            }

            $this->cart->clear();

            return $order->load('items');
        });
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
        do {
            $number = 'SACY-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
