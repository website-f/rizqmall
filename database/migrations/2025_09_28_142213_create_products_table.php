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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('store_category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['parent_id', 'is_active']);
        });
        
       Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color')->default('#6366f1');
            $table->timestamps();
        });

        Schema::create('variant_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Color", "Size", "Material"
            $table->string('slug')->unique();
            $table->enum('display_type', ['dropdown', 'color_swatch', 'image_swatch', 'button'])->default('button');
            $table->boolean('has_image')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

       Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            
            // Product Type
            $table->enum('type', ['product', 'service', 'pharmacy'])->default('product');
            $table->enum('product_type', ['simple', 'variable'])->default('simple');
            
            // Basic Info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description');
            
            // Pricing
            $table->decimal('regular_price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            
            // Inventory
            $table->string('sku')->unique()->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            
            // Physical Attributes (for products)
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            
            // Special Attributes
            $table->boolean('is_fragile')->default(false);
            $table->boolean('is_biodegradable')->default(false);
            $table->boolean('is_frozen')->default(false);
            $table->string('max_temperature')->nullable();
            $table->boolean('requires_prescription')->default(false); // For pharmacy
            $table->date('expiry_date')->nullable();
            $table->boolean('has_expiry')->default(false);
            
            // Service-specific fields
            $table->integer('service_duration')->nullable(); // in minutes
            $table->enum('service_availability', ['instant', 'scheduled', 'both'])->nullable();
            $table->json('service_days')->nullable(); // Available days
            $table->time('service_start_time')->nullable();
            $table->time('service_end_time')->nullable();
            
            // Pharmacy-specific
            $table->string('drug_code')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('active_ingredient')->nullable();
            $table->string('dosage_form')->nullable(); // tablet, syrup, injection
            $table->string('strength')->nullable(); // e.g., "500mg"
            
            // Product Identifiers
            $table->string('product_id_type')->nullable();
            $table->string('product_id_value')->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // Stats
            $table->integer('view_count')->default(0);
            $table->integer('sold_count')->default(0);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            
            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'status']);
            $table->index(['store_id', 'status']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('variant_types');
        Schema::dropIfExists('products');
    }
};
