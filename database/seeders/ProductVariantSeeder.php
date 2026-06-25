<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    public function run(?Product $product = null): void
    {
        if ($product) {
            $this->seedVariants($product);

            return;
        }

        Product::query()->each(function (Product $product) {
            $this->seedVariants($product);
        });
    }

    private function seedVariants(Product $product): void
    {
        $sizes = ['38', '39', '40', '41'];
        $colors = ['Black', 'Nude', 'Brown'];
        $heels = ['Flat', '2in', '3in'];

        $product->variants()->delete();
        $totalStock = 0;
        $variantIndex = 0;

        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                foreach ($heels as $heel) {
                    $variantIndex++;

                    if ($variantIndex % 3 === 0) {
                        continue;
                    }

                    $quantity = 2 + ($variantIndex % 7);
                    $totalStock += $quantity;

                    ProductVariant::query()->create([
                        'product_id' => $product->id,
                        'sku' => strtoupper("{$product->sku}-{$size}-".Str::slug($color, '').'-'.Str::slug($heel, '')),
                        'size' => $size,
                        'color' => $color,
                        'heel_length' => $heel,
                        'quantity' => $quantity,
                        'is_active' => true,
                    ]);
                }
            }
        }

        if ($totalStock === 0) {
            ProductVariant::query()->create([
                'product_id' => $product->id,
                'sku' => "{$product->sku}-38-BLK-FLAT",
                'size' => '38',
                'color' => 'Black',
                'heel_length' => 'Flat',
                'quantity' => 10,
                'is_active' => true,
            ]);

            $totalStock = (int) $product->variants()->sum('quantity');
        }

        $product->update(['quantity' => $totalStock]);
    }
}
