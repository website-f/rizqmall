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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete(); // Product category
            
            $table->string('name');
            $table->string('slug')->unique(); // For friendly URLs
            $table->text('short_description')->nullable(); // Brief intro
            $table->longText('description')->nullable();  // Full description (from Product Description tab)
        
            $table->decimal('regular_price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('sku')->unique()->nullable(); // Stock Keeping Unit
        
            // Inventory & Status
            $table->integer('stock_quantity')->default(0); // Total stock across all non-variant products
            $table->boolean('is_trackable')->default(true); // Inventory tracking
            $table->string('status')->default('draft'); // e.g., 'draft', 'published', 'archived'
        
            // Attributes (from the "Advanced" tab)
            $table->string('product_id_type')->nullable(); // e.g., ISBN, UPC, EAN, JAN
            $table->string('product_id_value')->nullable(); // The actual ID value
        
            // Attributes from 'Attributes' tab
            $table->boolean('is_fragile')->default(false);
            $table->boolean('is_biodegradable')->default(false);
            $table->boolean('is_frozen')->default(false);
            $table->string('max_temperature')->nullable();
            $table->timestamp('expiry_date')->nullable();
        
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
