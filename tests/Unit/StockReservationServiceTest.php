<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_orders_reduce_sellable_quantity(): void
    {
        $variant = $this->makeVariant(quantity: 2);
        $this->makePendingOrder($variant, quantity: 1);

        $sellable = app(StockReservationService::class)->sellableQuantity($variant->fresh());

        $this->assertSame(1, $sellable);
        $this->assertSame(1, $variant->fresh()->availableQuantity());
    }

    public function test_a_second_pending_order_cannot_take_the_last_held_unit(): void
    {
        $variant = $this->makeVariant(quantity: 1);
        $this->makePendingOrder($variant, quantity: 1);

        $this->assertSame(0, app(StockReservationService::class)->sellableQuantity($variant->fresh()));
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
