<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => CouponType::Percent,
                'value' => 10,
                'min_order_amount' => 100,
                'max_discount' => 100,
                'usage_limit' => 100,
            ],
            [
                'code' => 'SAVE50',
                'type' => CouponType::Fixed,
                'value' => 50,
                'min_order_amount' => 200,
                'max_discount' => null,
                'usage_limit' => 50,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::query()->updateOrCreate(
                ['code' => $coupon['code']],
                [
                    'type' => $coupon['type'],
                    'value' => $coupon['value'],
                    'min_order_amount' => $coupon['min_order_amount'],
                    'max_discount' => $coupon['max_discount'],
                    'usage_limit' => $coupon['usage_limit'],
                    'is_active' => true,
                ],
            );
        }
    }
}
