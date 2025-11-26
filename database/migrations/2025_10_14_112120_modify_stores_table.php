<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Check if auth_user_id column exists before dropping
            if (Schema::hasColumn('stores', 'auth_user_id')) {
                // Check if unique index exists using raw SQL
                $indexExists = DB::select("SHOW INDEX FROM stores WHERE Key_name = 'stores_auth_user_id_unique'");

                if (!empty($indexExists)) {
                    $table->dropUnique(['auth_user_id']);
                }

                $table->dropColumn('auth_user_id');
            }

            // Add proper foreign key to users table if it doesn't exist
            if (!Schema::hasColumn('stores', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            }
        });

        // Add indexes if they don't exist
        Schema::table('stores', function (Blueprint $table) {
            // Check if any index exists on user_id column
            $userIdIndex = DB::select("SHOW INDEX FROM stores WHERE Column_name = 'user_id'");
            if (empty($userIdIndex)) {
                $table->index('user_id');
            }

            // Business Information
            $table->string('business_registration_no')->nullable()->after('email');
            $table->string('tax_id')->nullable()->after('business_registration_no');

            // Social Media
            $table->string('facebook_url')->nullable()->after('description');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('twitter_url')->nullable()->after('instagram_url');
            $table->string('website_url')->nullable()->after('twitter_url');

            // Operating Hours
            $table->json('operating_hours')->nullable()->after('website_url');

            // Store Settings
            $table->boolean('allow_cod')->default(true)->after('operating_hours');
            $table->boolean('allow_online_payment')->default(true)->after('allow_cod');
            $table->decimal('minimum_order', 10, 2)->nullable()->after('allow_online_payment');
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('minimum_order');
            $table->decimal('free_delivery_threshold', 10, 2)->nullable()->after('delivery_fee');

            // Store Status
            $table->enum('status', ['pending', 'active', 'suspended', 'closed'])->default('active')->after('is_verified');
            $table->text('suspension_reason')->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('suspension_reason');

            // Statistics (cached for performance)
            $table->unsignedInteger('total_products')->default(0)->after('verified_at');
            $table->unsignedInteger('total_orders')->default(0)->after('total_products');
            $table->decimal('total_revenue', 15, 2)->default(0)->after('total_orders');
            $table->decimal('average_rating', 3, 2)->default(0)->after('total_revenue');
            $table->unsignedInteger('total_reviews')->default(0)->after('average_rating');

            // SEO
            $table->string('meta_title')->nullable()->after('total_reviews');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('slug');
            $table->index(['store_category_id', 'is_active', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'business_registration_no',
                'tax_id',
                'facebook_url',
                'instagram_url',
                'twitter_url',
                'website_url',
                'operating_hours',
                'allow_cod',
                'allow_online_payment',
                'minimum_order',
                'delivery_fee',
                'free_delivery_threshold',
                'status',
                'suspension_reason',
                'verified_at',
                'total_products',
                'total_orders',
                'total_revenue',
                'average_rating',
                'total_reviews',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ]);

            $table->unsignedBigInteger('auth_user_id')->unique();
        });
    }
};
