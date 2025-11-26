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
            $table->string('ssm_document')->nullable()->after('banner'); // SSM certificate upload
            $table->string('ic_document')->nullable()->after('ssm_document'); // IC/MyKad upload
            $table->string('business_registration_number')->nullable()->after('ic_document'); // Business registration number
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['ssm_document', 'ic_document', 'business_registration_number']);
        });
    }
};
