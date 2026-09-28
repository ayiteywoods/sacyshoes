<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\AdminReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportServiceQuantitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_paid_quantities_by_size_and_color_for_the_period(): void
    {
        $user = User::factory()->create(['role' => UserRole::Customer]);

        $paid = Order::query()->create([
            'order_number' => 'SACY-1001',
            'user_id' => $user->id,
            'subtotal' => 300,
            'delivery_fee' => 0,
            'tax' => 0,
            'total' => 300,
            'payment_status' => PaymentStatus::Paid,
            'status' => OrderStatus::Paid,
            'billing_full_name' => 'Ada Mensah',
            'billing_phone' => '0240000000',
            'billing_email' => 'ada@example.com',
            'billing_address' => '1 High Street',
            'billing_city' => 'Accra',
            'billing_country' => 'Ghana',
            'paid_at' => now()->subDay(),
        ]);

        OrderItem::query()->create([
            'order_id' => $paid->id,
            'product_name' => 'Classic Oxford',
            'product_sku' => 'OXF-1',
            'quantity' => 2,
            'unit_price' => 100,
            'total_price' => 200,
            'variant_options' => ['size' => '42', 'color' => 'Black'],
        ]);

        OrderItem::query()->create([
            'order_id' => $paid->id,
            'product_name' => 'Classic Oxford',
            'product_sku' => 'OXF-1',
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
            'variant_options' => ['size' => '43', 'color' => 'Brown'],
        ]);

        $pending = Order::query()->create([
            'order_number' => 'SACY-1002',
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
            'paid_at' => null,
        ]);

        OrderItem::query()->create([
            'order_id' => $pending->id,
            'product_name' => 'Classic Oxford',
            'product_sku' => 'OXF-1',
            'quantity' => 5,
            'unit_price' => 100,
            'total_price' => 100,
            'variant_options' => ['size' => '42', 'color' => 'Black'],
        ]);

        $reports = app(AdminReportService::class);
        $from = now()->subDays(7);
        $to = now();

        $sizes = $reports->quantitiesSoldBySize($from, $to);
        $colors = $reports->quantitiesSoldByColor($from, $to);
        $variants = $reports->quantitiesSoldByVariantAll($from, $to);

        $this->assertSame(3, $reports->unitsSoldForPeriod($from, $to));
        $this->assertSame(2, (int) $sizes->firstWhere('label', '42')->units_sold);
        $this->assertSame(1, (int) $sizes->firstWhere('label', '43')->units_sold);
        $this->assertSame(2, (int) $colors->firstWhere('label', 'Black')->units_sold);
        $this->assertSame(1, (int) $colors->firstWhere('label', 'Brown')->units_sold);
        $this->assertCount(2, $variants);
        $this->assertSame(2, (int) $variants->firstWhere('size', '42')->units_sold);
    }
}
