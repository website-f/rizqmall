<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $iconUpdates = [
            'marketplace'    => 'fas fa-boxes-stacked',
            'services'       => 'fas fa-briefcase',
            'pharmacy'       => 'fas fa-prescription-bottle-medical',
            'accommodation'  => 'fas fa-calendar-check',
            'premises'       => 'fas fa-building',
            'contractors'    => 'fas fa-helmet-safety',
            'food-catering'  => 'fas fa-utensils',
            'hardware'       => 'fas fa-screwdriver-wrench',
            'delivery'       => 'fas fa-truck-fast',
            'mobility'       => 'fas fa-car-side',
        ];

        foreach ($iconUpdates as $slug => $icon) {
            DB::table('store_categories')
                ->where('slug', $slug)
                ->update(['icon' => $icon]);
        }
    }

    public function down(): void
    {
        $iconUpdates = [
            'marketplace'    => 'fas fa-store',
            'services'       => 'fas fa-concierge-bell',
            'pharmacy'       => 'fas fa-pills',
            'accommodation'  => 'fas fa-calendar-check',
            'premises'       => 'fas fa-building',
            'contractors'    => 'fas fa-hard-hat',
            'food-catering'  => 'fas fa-utensils',
            'hardware'       => 'fas fa-tools',
            'delivery'       => 'fas fa-shipping-fast',
            'mobility'       => 'fas fa-car',
        ];

        foreach ($iconUpdates as $slug => $icon) {
            DB::table('store_categories')
                ->where('slug', $slug)
                ->update(['icon' => $icon]);
        }
    }
};
