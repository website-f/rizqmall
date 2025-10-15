<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This modifies the EXISTING users table to support both local and SSO users
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // SSO Integration (nullable - for users from subscription system)
            $table->unsignedBigInteger('subscription_user_id')->nullable()->unique()->after('id');
            
            // Make password nullable (for SSO users)
            $table->string('password')->nullable()->change();
            
            // User Type
            $table->enum('user_type', ['vendor', 'customer', 'admin'])->default('customer')->after('email');
            
            // Additional Info
            $table->string('phone')->nullable()->after('email_verified_at');
            $table->string('avatar')->nullable()->after('phone');
            
            // Account Status
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->boolean('email_verified')->default(false)->after('is_active');
            
            // Subscription Info (for vendors from subscription system)
            $table->string('subscription_status')->nullable()->after('email_verified');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_status');
            
            // Auth Type
            $table->enum('auth_type', ['local', 'sso'])->default('local')->after('subscription_expires_at');
            
            // Profile
            $table->text('bio')->nullable()->after('auth_type');
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            
            // Preferences
            $table->json('preferences')->nullable()->after('gender');
            
            // Security
            $table->string('last_login_ip')->nullable()->after('preferences');
            $table->timestamp('last_login_at')->nullable()->after('last_login_ip');
            
            // Soft Delete
            $table->softDeletes()->after('updated_at');
            
            // Indexes
            $table->index('subscription_user_id');
            $table->index('email');
            $table->index('user_type');
            $table->index('auth_type');
            $table->index(['user_type', 'is_active']);
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
                'user_type',
                'phone',
                'avatar',
                'is_active',
                'email_verified',
                'subscription_status',
                'subscription_expires_at',
                'auth_type',
                'bio',
                'date_of_birth',
                'gender',
                'preferences',
                'last_login_ip',
                'last_login_at',
            ]);
            $table->dropSoftDeletes();
        });
    }
};