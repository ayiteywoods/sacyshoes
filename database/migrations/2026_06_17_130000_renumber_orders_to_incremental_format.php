<?php

use App\Support\OrderNumberGenerator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        OrderNumberGenerator::renumberAll();
    }

    public function down(): void
    {
        // Order numbers cannot be restored to the previous SACY format.
    }
};
