<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update store categories - activate all except contractors
        DB::table('store_categories')->whereIn('slug', [
            'accommodation',
            'delivery',
            'taxi-rent'
        ])->update(['is_active' => true]);

        // Update Taxi & Rent to Mobility
        DB::table('store_categories')
            ->where('slug', 'taxi-rent')
            ->update([
                'name' => 'Mobility',
                'slug' => 'mobility',
                'icon' => 'fas fa-car',
                'description' => 'Transportation, taxi, and vehicle rental services',
            ]);

        // Update icons to FontAwesome
        $iconUpdates = [
            'marketplace' => ['icon' => 'fas fa-store', 'description' => 'Bulk orders and preorder products for events, businesses, and organizations'],
            'services' => ['icon' => 'fas fa-concierge-bell'],
            'pharmacy' => ['icon' => 'fas fa-pills'],
            'accommodation' => ['icon' => 'fas fa-calendar-check', 'description' => 'Rent properties, venues, and accommodations'],
            'premises' => ['icon' => 'fas fa-building'],
            'contractors' => ['icon' => 'fas fa-hard-hat'],
            'food-catering' => ['icon' => 'fas fa-utensils'],
            'hardware' => ['icon' => 'fas fa-tools'],
            'delivery' => ['icon' => 'fas fa-shipping-fast'],
        ];

        foreach ($iconUpdates as $slug => $data) {
            DB::table('store_categories')->where('slug', $slug)->update($data);
        }

        // Add marketplace fields to products table
        Schema::table('products', function (Blueprint $table) {
            // Marketplace/Bulk order fields
            $table->boolean('allow_bulk_order')->default(false)->after('allow_backorder');
            $table->integer('minimum_order_quantity')->default(1)->after('allow_bulk_order');
            $table->decimal('bulk_price', 10, 2)->nullable()->after('minimum_order_quantity');
            $table->integer('bulk_quantity_threshold')->nullable()->after('bulk_price');

            // Preorder fields
            $table->boolean('is_preorder')->default(false)->after('bulk_quantity_threshold');
            $table->date('preorder_release_date')->nullable()->after('is_preorder');
            $table->integer('preorder_limit')->nullable()->after('preorder_release_date');
            $table->text('preorder_note')->nullable()->after('preorder_limit');

            // Lead time for marketplace orders
            $table->integer('lead_time_days')->nullable()->after('preorder_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'allow_bulk_order',
                'minimum_order_quantity',
                'bulk_price',
                'bulk_quantity_threshold',
                'is_preorder',
                'preorder_release_date',
                'preorder_limit',
                'preorder_note',
                'lead_time_days',
            ]);
        });

        // Revert category changes
        DB::table('store_categories')->whereIn('slug', [
            'accommodation',
            'delivery'
        ])->update(['is_active' => false]);

        DB::table('store_categories')
            ->where('slug', 'mobility')
            ->update([
                'name' => 'Taxi & Rent',
                'slug' => 'taxi-rent',
                'icon' => 'uil uil-palette',
                'is_active' => false,
            ]);
    }
};
