<?php

namespace Database\Seeders;

use App\Models\ShippingOption;
use App\Models\ShippingRegion;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'Accra', 'is_accra' => true, 'sort_order' => 0],
            ['name' => 'Ashanti', 'is_accra' => false, 'sort_order' => 10],
            ['name' => 'Western', 'is_accra' => false, 'sort_order' => 20],
            ['name' => 'Eastern', 'is_accra' => false, 'sort_order' => 30],
            ['name' => 'Central', 'is_accra' => false, 'sort_order' => 40],
            ['name' => 'Northern', 'is_accra' => false, 'sort_order' => 50],
            ['name' => 'Upper East', 'is_accra' => false, 'sort_order' => 60],
            ['name' => 'Upper West', 'is_accra' => false, 'sort_order' => 70],
            ['name' => 'Volta', 'is_accra' => false, 'sort_order' => 80],
            ['name' => 'Bono', 'is_accra' => false, 'sort_order' => 90],
            ['name' => 'Bono East', 'is_accra' => false, 'sort_order' => 100],
            ['name' => 'Ahafo', 'is_accra' => false, 'sort_order' => 110],
            ['name' => 'Western North', 'is_accra' => false, 'sort_order' => 120],
            ['name' => 'Savannah', 'is_accra' => false, 'sort_order' => 130],
            ['name' => 'North East', 'is_accra' => false, 'sort_order' => 140],
            ['name' => 'Oti', 'is_accra' => false, 'sort_order' => 150],
        ];

        $deliveryOptions = [
            ['name' => 'OA', 'price' => 42, 'description' => 'Parcel delivery via OA Travel & Tour.'],
            ['name' => 'STC', 'price' => 42, 'description' => 'Delivery via State Transport Corporation.'],
            ['name' => 'VIP Parcel Office', 'price' => 42, 'description' => 'Delivery to VIP parcel office.'],
            ['name' => 'KEK', 'price' => 42, 'description' => 'Fast regional parcel delivery.'],
            ['name' => 'Station Cars (Delivery only)', 'price' => 10, 'description' => 'Station car delivery only.'],
            ['name' => 'FedEx', 'price' => 70, 'description' => 'FedEx parcel delivery.'],
        ];

        $optionNames = array_column($deliveryOptions, 'name');

        foreach ($regions as $regionData) {
            $region = ShippingRegion::query()->updateOrCreate(
                ['name' => $regionData['name']],
                [
                    'is_accra' => $regionData['is_accra'],
                    'is_active' => true,
                    'sort_order' => $regionData['sort_order'],
                ]
            );

            if ($region->is_accra) {
                $region->options()->update(['is_active' => false]);

                continue;
            }

            foreach ($deliveryOptions as $index => $option) {
                ShippingOption::query()->updateOrCreate(
                    [
                        'shipping_region_id' => $region->id,
                        'name' => $option['name'],
                    ],
                    [
                        'price' => $option['price'],
                        'description' => $option['description'],
                        'is_active' => true,
                        'sort_order' => $index,
                    ]
                );
            }

            $region->options()
                ->whereNotIn('name', $optionNames)
                ->update(['is_active' => false]);
        }

        ShippingRegion::query()
            ->whereIn('name', [
                'Outside Accra',
                'Greater Accra',
                'Ashanti Region',
            ])
            ->update(['is_active' => false]);
    }
}
