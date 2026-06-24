<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cart_items', 'product_variant_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained()
                    ->nullOnDelete();
            });
        }

        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('cart_id', 'cart_items_cart_id_index');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_id']);
            $table->unique(['cart_id', 'product_variant_id']);
        });

        if (! Schema::hasColumn('order_items', 'product_variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained()
                    ->nullOnDelete();
                $table->string('variant_sku')->nullable()->after('product_sku');
                $table->json('variant_options')->nullable()->after('variant_sku');
            });
        }

        Product::query()->each(function (Product $product) {
            if ($product->variants()->exists()) {
                return;
            }

            ProductVariant::query()->create([
                'product_id' => $product->id,
                'sku' => $product->sku.'-OS-DEF-FLAT',
                'size' => 'One Size',
                'color' => 'Default',
                'heel_length' => 'Flat',
                'quantity' => $product->quantity,
                'is_active' => true,
            ]);
        });

        if (Schema::hasTable('cart_items')) {
            $cartItems = DB::table('cart_items')
                ->whereNull('product_variant_id')
                ->orderBy('id')
                ->get();

            foreach ($cartItems as $item) {
                $variantId = ProductVariant::query()
                    ->where('product_id', $item->product_id)
                    ->value('id');

                if ($variantId) {
                    DB::table('cart_items')
                        ->where('id', $item->id)
                        ->update(['product_variant_id' => $variantId]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn(['variant_sku', 'variant_options']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_variant_id']);
            $table->dropConstrainedForeignId('product_variant_id');
            $table->unique(['cart_id', 'product_id']);
            $table->dropIndex('cart_items_cart_id_index');
        });
    }
};
