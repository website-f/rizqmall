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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., "Color", "Size", "Processor/Chipset"
            $table->string('type')->default('text'); // e.g., 'select', 'color-swatch', 'text'
            $table->timestamps();
        });
        
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->string('value'); // The actual value of the attribute, e.g., "Blue", "Apple M1 chip"
            $table->string('unit')->nullable(); // e.g., "GB", "cores", "nits"
            $table->timestamps();
            $table->unique(['product_id', 'attribute_id']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Blue / Large"
            $table->string('sku')->unique()->nullable();
        
            // Variant-specific overrides (if different from the parent product)
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('color_hex')->nullable(); // If the variant is based on color
            
            $table->timestamps();
        });

         Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete(); // Optional: link to a variant (e.g., color)
            $table->string('path');
            $table->integer('order')->default(0); // To control display order
            $table->timestamps();
        });
        
        // Pivot table for many-to-many relationship between products and tags
        Schema::create('product_tag', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'tag_id']);
        });
        
        // New table to link specific options (e.g., "Small", "Blue") to a product variant.
        // This is necessary to build the Variant Name ("Blue / Large")
        Schema::create('variant_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            // This column holds the actual value, e.g., 'Blue' or 'Large'
            $table->string('option_name'); // e.g., 'Color', 'Size'
            $table->string('option_value'); // e.g., 'Red', 'Small'
            $table->timestamps();
            $table->unique(['variant_id', 'option_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
