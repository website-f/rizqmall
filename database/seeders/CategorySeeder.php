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
                'icon' => 'uil uil-shopping-bag',
                'description' => 'Sell physical products and goods',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'icon' => 'uil uil-mobile-android',
                'description' => 'Offer professional services',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pharmacy',
                'slug' => 'pharmacy',
                'icon' => 'uil uil-monitor',
                'description' => 'Sell pharmaceutical products and medications',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Booking & Rent',
                'slug' => 'accommodation',
                'icon' => 'uil uil-watch-alt',
                'description' => 'Rent properties and accommodations',
                'is_active' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Premises',
                'slug' => 'premises',
                'icon' => 'uil uil-estate',
                'description' => 'Commercial and residential properties',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Contractors',
                'slug' => 'contractors',
                'icon' => 'uil uil-lamp',
                'description' => 'Construction and renovation services',
                'is_active' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Food & Catering',
                'slug' => 'food-catering',
                'icon' => 'uil uil-gift',
                'description' => 'Food delivery and catering services',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Hardware',
                'slug' => 'hardware',
                'icon' => 'uil uil-wrench',
                'description' => 'Tools and hardware supplies',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Delivery',
                'slug' => 'delivery',
                'icon' => 'uil uil-plane-departure',
                'description' => 'Courier and delivery services',
                'is_active' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Taxi & Rent',
                'slug' => 'taxi-rent',
                'icon' => 'uil uil-palette',
                'description' => 'Transportation and vehicle rental',
                'is_active' => false,
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            StoreCategory::create($category);
        }
    }
}
