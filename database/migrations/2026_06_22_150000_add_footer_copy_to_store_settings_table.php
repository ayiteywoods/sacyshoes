<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('footer_tagline')->nullable()->after('about_hero_description');
            $table->string('footer_subline')->nullable()->after('footer_tagline');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['footer_tagline', 'footer_subline']);
        });
    }
};
