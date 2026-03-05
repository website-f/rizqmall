<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add quote fields to products table
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('allow_quote_request')->default(false)->after('lead_time_days');
            $table->integer('quote_threshold_quantity')->nullable()->after('allow_quote_request');
        });

        // Add order_type to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type', 20)->default('retail')->after('store_id');
        });

        // Create bulk_quotes table
        Schema::create('bulk_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->integer('requested_quantity');
            $table->text('buyer_notes')->nullable();
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->decimal('quoted_total', 10, 2)->nullable();
            $table->text('vendor_notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_quotes');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['allow_quote_request', 'quote_threshold_quantity']);
        });
    }
};
