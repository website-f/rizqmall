<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Track if this is the primary (free) store
            if (!Schema::hasColumn('stores', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('user_id');
            }

            // Store subscription tier (for future expansion)
            if (!Schema::hasColumn('stores', 'subscription_tier')) {
                $table->string('subscription_tier')->default('basic')->after('is_primary');
            }

            // Store quota tracking
            if (!Schema::hasColumn('stores', 'product_limit')) {
                $table->integer('product_limit')->nullable()->after('subscription_tier');
            }

            if (!Schema::hasColumn('stores', 'image_storage_mb')) {
                $table->integer('image_storage_mb')->nullable()->after('product_limit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['is_primary', 'subscription_tier', 'product_limit', 'image_storage_mb']);
        });
    }
};
