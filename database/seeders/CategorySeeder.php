<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\StoreCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Marketplace',
                'slug' => 'marketplace',
                'icon' => 'fas fa-store',
                'description' => 'Bulk orders and preorder products for events, businesses, and organizations',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'icon' => 'fas fa-concierge-bell',
                'description' => 'Professional services and expertise',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pharmacy',
                'slug' => 'pharmacy',
                'icon' => 'fas fa-pills',
                'description' => 'Pharmaceutical products and health medications',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Booking & Rent',
                'slug' => 'accommodation',
                'icon' => 'fas fa-calendar-check',
                'description' => 'Rent properties, venues, and accommodations',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Premises',
                'slug' => 'premises',
                'icon' => 'fas fa-building',
                'description' => 'Commercial and residential properties for sale or rent',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Contractors',
                'slug' => 'contractors',
                'icon' => 'fas fa-hard-hat',
                'description' => 'Construction and renovation services',
                'is_active' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Food & Catering',
                'slug' => 'food-catering',
                'icon' => 'fas fa-utensils',
                'description' => 'Food delivery, catering, and culinary services',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Hardware',
                'slug' => 'hardware',
                'icon' => 'fas fa-tools',
                'description' => 'Tools, equipment, and hardware supplies',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Delivery',
                'slug' => 'delivery',
                'icon' => 'fas fa-shipping-fast',
                'description' => 'Courier and delivery services',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Mobility',
                'slug' => 'mobility',
                'icon' => 'fas fa-car',
                'description' => 'Transportation, taxi, and vehicle rental services',
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            StoreCategory::create($category);
        }
    }
}
