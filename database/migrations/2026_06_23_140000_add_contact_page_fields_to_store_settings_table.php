<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->string('contact_page_email')->nullable()->after('contact_website');
            $table->string('contact_page_phone')->nullable()->after('contact_page_email');
            $table->string('contact_page_phone_alt')->nullable()->after('contact_page_phone');
            $table->text('contact_page_address')->nullable()->after('contact_page_phone_alt');
            $table->string('contact_page_hours_days')->nullable()->after('contact_page_address');
            $table->string('contact_page_hours_time')->nullable()->after('contact_page_hours_days');
            $table->string('contact_page_hours_note')->nullable()->after('contact_page_hours_time');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'contact_page_email',
                'contact_page_phone',
                'contact_page_phone_alt',
                'contact_page_address',
                'contact_page_hours_days',
                'contact_page_hours_time',
                'contact_page_hours_note',
            ]);
        });
    }
};
