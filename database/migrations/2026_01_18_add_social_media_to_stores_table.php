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
            if (!Schema::hasColumn('stores', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('stores', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('facebook_url');
            }
            if (!Schema::hasColumn('stores', 'twitter_url')) {
                $table->string('twitter_url')->nullable()->after('instagram_url');
            }
            if (!Schema::hasColumn('stores', 'tiktok_url')) {
                $table->string('tiktok_url')->nullable()->after('twitter_url');
            }
            if (!Schema::hasColumn('stores', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('tiktok_url');
            }
            if (!Schema::hasColumn('stores', 'whatsapp_number')) {
                $table->string('whatsapp_number')->nullable()->after('youtube_url');
            }
            if (!Schema::hasColumn('stores', 'telegram_url')) {
                $table->string('telegram_url')->nullable()->after('whatsapp_number');
            }
            if (!Schema::hasColumn('stores', 'website_url')) {
                $table->string('website_url')->nullable()->after('telegram_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url',
                'instagram_url',
                'twitter_url',
                'tiktok_url',
                'youtube_url',
                'whatsapp_number',
                'telegram_url',
                'website_url',
            ]);
        });
    }
};
