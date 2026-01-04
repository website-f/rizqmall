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
        Schema::create('vendor_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sandbox_referral_id')->nullable()->comment('ID from sandbox referrals table');
            $table->string('join_method')->default('direct'); // direct, qr_scan, ref_code, store_page
            $table->string('referral_code')->nullable()->comment('The code used to join');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            // Ensure a customer can only be a member of a store once
            $table->unique(['store_id', 'customer_id']);
        });

        // Add member referral code to stores
        Schema::table('stores', function (Blueprint $table) {
            $table->string('member_ref_code', 10)->nullable()->unique()->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('member_ref_code');
        });

        Schema::dropIfExists('vendor_members');
    }
};
