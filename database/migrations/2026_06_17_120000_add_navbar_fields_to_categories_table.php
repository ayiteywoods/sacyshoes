<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('show_in_navbar')->default(false)->after('status');
            $table->unsignedInteger('navbar_sort_order')->default(0)->after('show_in_navbar');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['show_in_navbar', 'navbar_sort_order']);
        });
    }
};
