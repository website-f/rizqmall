<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Drop old auth_user_id if it exists
            if (Schema::hasColumn('carts', 'auth_user_id')) {
                $table->dropColumn('auth_user_id');
            }

            // Add proper foreign key (nullable for guest carts) if it doesn't exist
            if (!Schema::hasColumn('carts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }

            // Add session_id if it doesn't exist
            if (!Schema::hasColumn('carts', 'session_id')) {
                $table->string('session_id')->nullable();
            }

            // Add merged_at if it doesn't exist
            if (!Schema::hasColumn('carts', 'merged_at')) {
                $table->timestamp('merged_at')->nullable();
            }
        });

        // Add indexes if they don't exist using raw SQL check
        Schema::table('carts', function (Blueprint $table) {
            $userIdIndex = DB::select("SHOW INDEX FROM carts WHERE Column_name = 'user_id'");
            if (empty($userIdIndex)) {
                $table->index('user_id');
            }

            $sessionIdIndex = DB::select("SHOW INDEX FROM carts WHERE Column_name = 'session_id'");
            if (empty($sessionIdIndex)) {
                $table->index('session_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->unsignedBigInteger('auth_user_id');
        });
    }
};
