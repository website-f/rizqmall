<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            // Allow flexible delivery types (standard/express/pickup/etc.)
            DB::statement("ALTER TABLE orders MODIFY delivery_type VARCHAR(20) NULL");

            // Optional: normalize legacy values
            DB::statement("UPDATE orders SET delivery_type = 'standard' WHERE delivery_type = 'delivery'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            DB::statement("ALTER TABLE orders MODIFY delivery_type ENUM('pickup', 'delivery') DEFAULT 'delivery'");
        }
    }
};
