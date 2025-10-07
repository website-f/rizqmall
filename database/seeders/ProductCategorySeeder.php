<?php

namespace Database\Seeders;

use App\Models\StoreCategory;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $marketplace = StoreCategory::where('slug', 'marketplace')->first();
        $services = StoreCategory::where('slug', 'services')->first();
        $pharmacy = StoreCategory::where('slug', 'pharmacy')->first();

        // Marketplace Categories
        $marketplaceCategories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'store_category_id' => $marketplace->id, 'sort_order' => 1],
            ['name' => 'Fashion & Apparel', 'slug' => 'fashion-apparel', 'store_category_id' => $marketplace->id, 'sort_order' => 2],
            ['name' => 'Home & Living', 'slug' => 'home-living', 'store_category_id' => $marketplace->id, 'sort_order' => 3],
            ['name' => 'Health & Beauty', 'slug' => 'health-beauty', 'store_category_id' => $marketplace->id, 'sort_order' => 4],
            ['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors', 'store_category_id' => $marketplace->id, 'sort_order' => 5],
            ['name' => 'Books & Stationery', 'slug' => 'books-stationery', 'store_category_id' => $marketplace->id, 'sort_order' => 6],
            ['name' => 'Toys & Hobbies', 'slug' => 'toys-hobbies', 'store_category_id' => $marketplace->id, 'sort_order' => 7],
            ['name' => 'Automotive', 'slug' => 'automotive', 'store_category_id' => $marketplace->id, 'sort_order' => 8],
            ['name' => 'Food & Beverages', 'slug' => 'food-beverages', 'store_category_id' => $marketplace->id, 'sort_order' => 9],
            ['name' => 'Pet Supplies', 'slug' => 'pet-supplies', 'store_category_id' => $marketplace->id, 'sort_order' => 10],
        ];

        // Services Categories
        $servicesCategories = [
            ['name' => 'Home Services', 'slug' => 'home-services', 'store_category_id' => $services->id, 'sort_order' => 1],
            ['name' => 'Professional Services', 'slug' => 'professional-services', 'store_category_id' => $services->id, 'sort_order' => 2],
            ['name' => 'Beauty & Wellness', 'slug' => 'beauty-wellness', 'store_category_id' => $services->id, 'sort_order' => 3],
            ['name' => 'Education & Training', 'slug' => 'education-training', 'store_category_id' => $services->id, 'sort_order' => 4],
            ['name' => 'Events & Entertainment', 'slug' => 'events-entertainment', 'store_category_id' => $services->id, 'sort_order' => 5],
            ['name' => 'IT & Digital Services', 'slug' => 'it-digital', 'store_category_id' => $services->id, 'sort_order' => 6],
            ['name' => 'Repair & Maintenance', 'slug' => 'repair-maintenance', 'store_category_id' => $services->id, 'sort_order' => 7],
            ['name' => 'Automotive Services', 'slug' => 'automotive-services', 'store_category_id' => $services->id, 'sort_order' => 8],
        ];

        // Pharmacy Categories
        $pharmacyCategories = [
            ['name' => 'Prescription Medicines', 'slug' => 'prescription-medicines', 'store_category_id' => $pharmacy->id, 'sort_order' => 1],
            ['name' => 'Over-the-Counter', 'slug' => 'over-counter', 'store_category_id' => $pharmacy->id, 'sort_order' => 2],
            ['name' => 'Vitamins & Supplements', 'slug' => 'vitamins-supplements', 'store_category_id' => $pharmacy->id, 'sort_order' => 3],
            ['name' => 'First Aid', 'slug' => 'first-aid', 'store_category_id' => $pharmacy->id, 'sort_order' => 4],
            ['name' => 'Personal Care', 'slug' => 'personal-care', 'store_category_id' => $pharmacy->id, 'sort_order' => 5],
            ['name' => 'Baby & Mother Care', 'slug' => 'baby-mother-care', 'store_category_id' => $pharmacy->id, 'sort_order' => 6],
            ['name' => 'Medical Devices', 'slug' => 'medical-devices', 'store_category_id' => $pharmacy->id, 'sort_order' => 7],
            ['name' => 'Diabetes Care', 'slug' => 'diabetes-care', 'store_category_id' => $pharmacy->id, 'sort_order' => 8],
        ];

        // Create all categories
        foreach (array_merge($marketplaceCategories, $servicesCategories, $pharmacyCategories) as $category) {
            ProductCategory::create($category);
        }

        // Create some subcategories for Electronics (as example)
        $electronics = ProductCategory::where('slug', 'electronics')->first();
        if ($electronics) {
            $subcategories = [
                ['name' => 'Smartphones', 'slug' => 'smartphones', 'parent_id' => $electronics->id, 'store_category_id' => $marketplace->id],
                ['name' => 'Laptops', 'slug' => 'laptops', 'parent_id' => $electronics->id, 'store_category_id' => $marketplace->id],
                ['name' => 'Tablets', 'slug' => 'tablets', 'parent_id' => $electronics->id, 'store_category_id' => $marketplace->id],
                ['name' => 'Audio & Headphones', 'slug' => 'audio-headphones', 'parent_id' => $electronics->id, 'store_category_id' => $marketplace->id],
                ['name' => 'Cameras', 'slug' => 'cameras', 'parent_id' => $electronics->id, 'store_category_id' => $marketplace->id],
            ];

            foreach ($subcategories as $sub) {
                ProductCategory::create($sub);
            }
        }
    }
}
