<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_accra')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('shipping_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_region_id')->constrained('shipping_regions')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['shipping_region_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_options');
        Schema::dropIfExists('shipping_regions');
    }
};
