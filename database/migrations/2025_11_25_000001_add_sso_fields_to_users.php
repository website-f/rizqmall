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
        Schema::table('users', function (Blueprint $table) {
            // SSO and Sandbox integration fields
            if (!Schema::hasColumn('users', 'subscription_user_id')) {
                $table->unsignedBigInteger('subscription_user_id')->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'auth_type')) {
                $table->enum('auth_type', ['sso', 'local'])->default('sso')->after('password');
            }

            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['vendor', 'customer', 'admin'])->default('customer')->after('auth_type');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('avatar');
            }

            if (!Schema::hasColumn('users', 'email_verified')) {
                $table->boolean('email_verified')->default(false)->after('is_active');
            }

            // Subscription tracking (synced from Sandbox)
            if (!Schema::hasColumn('users', 'subscription_status')) {
                $table->enum('subscription_status', ['active', 'expired', 'none'])->default('none')->after('email_verified');
            }

            if (!Schema::hasColumn('users', 'subscription_expires_at')) {
                $table->timestamp('subscription_expires_at')->nullable()->after('subscription_status');
            }

            // Additional profile fields
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('subscription_expires_at');
            }

            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('bio');
            }

            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            }

            if (!Schema::hasColumn('users', 'preferences')) {
                $table->json('preferences')->nullable()->after('gender');
            }

            // Tracking fields
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable()->after('preferences');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('last_login_ip');
            }

            if (!Schema::hasColumn('users', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('last_login_at');
            }

            // Soft deletes
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_user_id',
                'auth_type',
                'user_type',
                'phone',
                'avatar',
                'is_active',
                'email_verified',
                'subscription_status',
                'subscription_expires_at',
                'bio',
                'date_of_birth',
                'gender',
                'preferences',
                'last_login_ip',
                'last_login_at',
                'last_sync_at',
                'deleted_at',
            ]);
        });
    }
};
