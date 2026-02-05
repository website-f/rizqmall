<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'booking_fee')) {
                $table->decimal('booking_fee', 10, 2)->nullable()->after('service_end_time');
            }
            if (!Schema::hasColumn('products', 'package_price')) {
                $table->decimal('package_price', 10, 2)->nullable()->after('booking_fee');
            }
            if (!Schema::hasColumn('products', 'package_name')) {
                $table->string('package_name')->nullable()->after('package_price');
            }
            if (!Schema::hasColumn('products', 'package_details')) {
                $table->text('package_details')->nullable()->after('package_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'booking_fee',
                'package_price',
                'package_name',
                'package_details',
            ]);
        });
    }
};
