<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('reserved_quantity')->default(0)->after('quantity');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->timestamp('reserved_until')->nullable()->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('reserved_until');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('reserved_quantity');
        });
    }
};
