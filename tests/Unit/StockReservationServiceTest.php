<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderPaymentService;
use App\Services\PaystackService;
use App\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpaid_orders_do_not_hide_stock_until_it_is_reserved(): void
    {
        $variant = $this->makeVariant(quantity: 2);
        $this->makePendingOrder($variant, quantity: 1);

        $this->assertSame(2, app(StockReservationService::class)->sellableQuantity($variant->fresh()));
        $this->assertTrue($variant->product->fresh()->isInStock());
    }

    public function test_reserving_an_unpaid_order_takes_units_out_of_inventory(): void
    {
        $variant = $this->makeVariant(quantity: 2);
        $order = $this->makePendingOrder($variant, quantity: 1);

        app(StockReservationService::class)->reserveOrder($order);

        $this->assertSame(1, (int) $variant->fresh()->quantity);
        $this->assertSame(1, app(StockReservationService::class)->sellableQuantity($variant->fresh()));
        $this->assertTrue($order->fresh()->stock_reserved);
    }

    public function test_a_second_checkout_cannot_take_the_last_reserved_unit(): void
    {
        $variant = $this->makeVariant(quantity: 1);
        $order = $this->makePendingOrder($variant, quantity: 1);

        app(StockReservationService::class)->reserveOrder($order);

        $this->assertSame(0, app(StockReservationService::class)->sellableQuantity($variant->fresh()));
        $this->assertFalse($variant->product->fresh()->isInStock());
    }

    public function test_abandoned_unpaid_orders_return_stock_after_the_hold_window(): void
    {
        $variant = $this->makeVariant(quantity: 2);
        $order = $this->makePendingOrder($variant, quantity: 2);
        app(StockReservationService::class)->reserveOrder($order);
        $order->forceFill(['created_at' => now()->subHours(2)])->save();

        $released = app(StockReservationService::class)->releaseExpiredReservations();

        $this->assertSame(1, $released);
        $this->assertSame(2, (int) $variant->fresh()->quantity);
        $this->assertSame(2, app(StockReservationService::class)->sellableQuantity($variant->fresh()));
        $this->assertTrue($variant->product->fresh()->isInStock());
        $this->assertFalse($order->fresh()->stock_reserved);
    }

    public function test_payment_does_not_decrement_stock_twice_after_reservation(): void
    {
        $variant = $this->makeVariant(quantity: 3);
        $order = $this->makePendingOrder($variant, quantity: 2);
        $stock = app(StockReservationService::class);
        $stock->reserveOrder($order);

        $fulfilled = $stock->fulfillPaidOrder($order->fresh()->load('items'));

        $this->assertTrue($fulfilled);
        $this->assertSame(1, (int) $variant->fresh()->quantity);
        $this->assertFalse($order->fresh()->stock_reserved);
    }

    public function test_late_payment_after_release_takes_remaining_on_hand_stock(): void
    {
        $variant = $this->makeVariant(quantity: 2);
        $order = $this->makePendingOrder($variant, quantity: 2);
        $stock = app(StockReservationService::class);
        $stock->reserveOrder($order);
        $order->forceFill(['created_at' => now()->subHours(2)])->save();
        $stock->releaseExpiredReservations();

        $fulfilled = $stock->fulfillPaidOrder($order->fresh()->load('items'));

        $this->assertTrue($fulfilled);
        $this->assertSame(0, (int) $variant->fresh()->quantity);
    }

    public function test_second_customer_cannot_pay_after_the_last_item_is_already_paid_for(): void
    {
        $variant = $this->makeVariant(quantity: 1);
        $first = $this->makePendingOrder($variant, quantity: 1);
        $second = $this->makePendingOrder($variant, quantity: 1);
        $stock = app(StockReservationService::class);

        $stock->reserveOrder($first);
        $stock->fulfillPaidOrder($first->fresh()->load('items'));

        $this->expectException(InsufficientStockException::class);
        $stock->fulfillPaidOrder($second->fresh()->load('items'), allowPartial: false);
    }

    public function test_mark_as_paid_rejects_and_refunds_when_the_item_was_already_sold(): void
    {
        $this->mock(PaystackService::class, function ($mock) {
            $mock->shouldReceive('refund')->once()->andReturn(['id' => 99]);
        });

        $variant = $this->makeVariant(quantity: 1);
        $first = $this->makePendingOrder($variant, quantity: 1);
        $second = $this->makePendingOrder($variant, quantity: 1);
        $stock = app(StockReservationService::class);

        $stock->reserveOrder($first);

        $firstPayment = Payment::query()->create([
            'order_id' => $first->id,
            'user_id' => $first->user_id,
            'reference' => 'PSK-'.$first->order_number,
            'provider' => 'paystack',
            'amount' => $first->total,
            'currency' => 'GHS',
            'status' => PaymentStatus::Pending,
        ]);

        $secondPayment = Payment::query()->create([
            'order_id' => $second->id,
            'user_id' => $second->user_id,
            'reference' => 'PSK-'.$second->order_number,
            'provider' => 'paystack',
            'amount' => $second->total,
            'currency' => 'GHS',
            'status' => PaymentStatus::Pending,
        ]);

        $payments = app(OrderPaymentService::class);
        $payments->markAsPaid($first, $firstPayment, ['id' => 'txn_first']);
        $payments->markAsPaid($second, $secondPayment, ['id' => 'txn_sold_out']);

        $second->refresh();
        $secondPayment->refresh();
        $first->refresh();

        $this->assertSame(PaymentStatus::Paid, $first->payment_status);
        $this->assertSame(PaymentStatus::Refunded, $second->payment_status);
        $this->assertSame(OrderStatus::Cancelled, $second->status);
        $this->assertTrue((bool) data_get($secondPayment->metadata, 'stock_unavailable'));
        $this->assertSame(0, (int) $variant->fresh()->quantity);
    }

    public function test_payment_fulfillment_decrements_on_hand_stock(): void
    {
        $variant = $this->makeVariant(quantity: 3);
        $order = $this->makePendingOrder($variant, quantity: 2);

        $fulfilled = app(StockReservationService::class)->fulfillOrderItems($order->items);

        $this->assertTrue($fulfilled);
        $this->assertSame(1, (int) $variant->fresh()->quantity);
        $this->assertSame(1, (int) $variant->product->fresh()->quantity);
    }

    public function test_partial_fulfillment_takes_remaining_units_instead_of_leaving_stock(): void
    {
        $variant = $this->makeVariant(quantity: 1);
        $order = $this->makePendingOrder($variant, quantity: 2);

        $fulfilled = app(StockReservationService::class)->fulfillOrderItems($order->items, allowPartial: true);

        $this->assertFalse($fulfilled);
        $this->assertSame(0, (int) $variant->fresh()->quantity);
    }

    private function makeVariant(int $quantity): ProductVariant
    {
        $category = Category::query()->create([
            'name' => 'Formal',
            'slug' => 'formal-'.uniqid(),
            'status' => 'active',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Oxford',
            'slug' => 'oxford-'.uniqid(),
            'sku' => 'OXF-'.uniqid(),
            'price' => 100,
            'quantity' => $quantity,
            'status' => ProductStatus::Active,
        ]);

        return ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => 'OXF-BLK-'.uniqid(),
            'size' => '42',
            'color' => 'Black',
            'heel_length' => null,
            'quantity' => $quantity,
            'is_active' => true,
        ]);
    }

    private function makePendingOrder(ProductVariant $variant, int $quantity): Order
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);

        $order = Order::query()->create([
            'order_number' => 'SACY-'.fake()->unique()->numerify('####'),
            'user_id' => $user->id,
            'subtotal' => 100,
            'delivery_fee' => 0,
            'tax' => 0,
            'total' => 100,
            'payment_status' => PaymentStatus::Pending,
            'status' => OrderStatus::PendingPayment,
            'billing_full_name' => 'Ada Mensah',
            'billing_phone' => '0240000000',
            'billing_email' => 'ada@example.com',
            'billing_address' => '1 High Street',
            'billing_city' => 'Accra',
            'billing_country' => 'Ghana',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'product_sku' => $variant->product->sku,
            'quantity' => $quantity,
            'unit_price' => 100,
            'total_price' => 100 * $quantity,
            'variant_options' => ['size' => $variant->size, 'color' => $variant->color],
        ]);

        return $order->load('items');
    }
}
