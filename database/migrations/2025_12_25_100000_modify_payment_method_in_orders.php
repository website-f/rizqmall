<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change payment_method from ENUM to VARCHAR to support various payment methods
        Schema::table('orders', function (Blueprint $table) {
            // In MySQL, we need to alter the column type
            DB::statement("ALTER TABLE orders MODIFY payment_method VARCHAR(50) NULL");
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod', 'online', 'wallet') NULL");
        });
    }
};
