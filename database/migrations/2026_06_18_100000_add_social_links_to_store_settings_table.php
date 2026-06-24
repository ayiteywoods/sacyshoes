<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('social_facebook')->nullable()->after('about_hero_description');
            $table->string('social_instagram')->nullable()->after('social_facebook');
            $table->string('social_tiktok')->nullable()->after('social_instagram');
            $table->string('social_x')->nullable()->after('social_tiktok');
            $table->string('social_youtube')->nullable()->after('social_x');
            $table->string('social_whatsapp')->nullable()->after('social_youtube');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'social_facebook',
                'social_instagram',
                'social_tiktok',
                'social_x',
                'social_youtube',
                'social_whatsapp',
            ]);
        });
    }
};
