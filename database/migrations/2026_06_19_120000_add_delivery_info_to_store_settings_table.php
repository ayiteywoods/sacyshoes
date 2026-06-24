<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('delivery_shipping_note')->nullable()->after('about_hero_description');
            $table->text('delivery_info_accra')->nullable()->after('delivery_shipping_note');
            $table->text('delivery_info_regional')->nullable()->after('delivery_info_accra');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_shipping_note',
                'delivery_info_accra',
                'delivery_info_regional',
            ]);
        });
    }
};
