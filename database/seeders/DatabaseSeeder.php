<?php

namespace Database\Seeders;

use App\Enums\CategoryStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@sacyshoes.com'],
            [
                'first_name' => 'Sacy',
                'last_name' => 'Admin',
                'name' => 'Sacy Admin',
                'phone' => '0200000000',
                'password' => 'password',
                'role' => UserRole::Admin,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $categories = [
            ['name' => 'Sneakers', 'description' => 'Casual and sporty sneakers for everyday wear.', 'image' => 'images/brand/sneakers.jpg', 'navbar_sort_order' => 1],
            ['name' => 'Formal', 'description' => 'Elegant formal shoes for office and events.', 'image' => 'images/brand/shoes.jpg', 'navbar_sort_order' => 2],
            ['name' => 'Sandals', 'description' => 'Comfortable sandals for warm weather.', 'image' => 'images/brand/sandals.jpg', 'navbar_sort_order' => 3],
            ['name' => 'Boots', 'description' => 'Durable boots for all seasons.', 'image' => 'images/brand/boots.jpg', 'navbar_sort_order' => 4],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'image' => $categoryData['image'],
                    'status' => CategoryStatus::Active,
                    'show_in_navbar' => true,
                    'navbar_sort_order' => $categoryData['navbar_sort_order'],
                    'shop_sort_order' => $categoryData['navbar_sort_order'],
                ]
            );

            for ($i = 1; $i <= 3; $i++) {
                $name = "{$categoryData['name']} Style {$i}";
                $sku = strtoupper(Str::slug($categoryData['name'], ''))."-00{$i}";
                $product = Product::query()->updateOrCreate(
                    ['sku' => $sku],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'slug' => Str::slug($name),
                        'price' => fake()->randomFloat(2, 150, 450),
                        'discount_price' => $i === 1 ? fake()->randomFloat(2, 120, 200) : null,
                        'description' => 'Premium quality footwear from the Sacy Shoes collection.',
                        'quantity' => 0,
                        'status' => ProductStatus::Active,
                    ]
                );

                $this->call(ProductVariantSeeder::class, false, ['product' => $product]);
            }
        }

        $this->call(ShippingSeeder::class);
        $this->call(CouponSeeder::class);
        $this->call(HomeContentSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(EmailTemplateSeeder::class);
    }
}
