<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete stores that reference non-existent users
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $orphanedStores = DB::table('stores')
            ->whereNotIn('user_id', function ($query) {
                $query->select('id')->from('users');
            })
            ->get();

        foreach ($orphanedStores as $store) {
            Log::warning('Deleting orphaned store', [
                'store_id' => $store->id,
                'user_id' => $store->user_id,
                'store_name' => $store->name ?? 'N/A',
            ]);
        }

        DB::table('stores')
            ->whereNotIn('user_id', function ($query) {
                $query->select('id')->from('users');
            })
            ->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore deleted data
    }
};
