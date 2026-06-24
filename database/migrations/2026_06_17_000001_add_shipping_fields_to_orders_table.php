<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_full_name')->nullable()->after('billing_country');
            $table->string('shipping_phone')->nullable()->after('shipping_full_name');
            $table->string('shipping_email')->nullable()->after('shipping_phone');
            $table->string('shipping_address')->nullable()->after('shipping_email');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_country')->nullable()->after('shipping_city');

            $table->foreignId('shipping_region_id')->nullable()->after('shipping_country')
                ->constrained('shipping_regions')->nullOnDelete();
            $table->foreignId('shipping_option_id')->nullable()->after('shipping_region_id')
                ->constrained('shipping_options')->nullOnDelete();

            $table->string('shipping_region_name')->nullable()->after('shipping_option_id');
            $table->string('shipping_option_name')->nullable()->after('shipping_region_name');
            $table->decimal('shipping_fee', 12, 2)->default(0)->after('shipping_option_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_option_id');
            $table->dropConstrainedForeignId('shipping_region_id');
            $table->dropColumn([
                'shipping_full_name',
                'shipping_phone',
                'shipping_email',
                'shipping_address',
                'shipping_city',
                'shipping_country',
                'shipping_region_name',
                'shipping_option_name',
                'shipping_fee',
            ]);
        });
    }
};
