<?php

namespace Database\Seeders;

use App\Models\StoreCategory;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class FoodCateringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Enable Food & Catering Store Category
        $foodCatering = StoreCategory::where('slug', 'food-catering')->first();
        if ($foodCatering) {
            $foodCatering->update(['is_active' => true]);
        }

        // 2. Add Product Categories for Food & Catering
        $foodCategories = [
            ['name' => 'Malay Cuisine', 'slug' => 'malay-cuisine', 'store_category_id' => $foodCatering->id ?? 7, 'sort_order' => 1],
            ['name' => 'Chinese Cuisine', 'slug' => 'chinese-cuisine', 'store_category_id' => $foodCatering->id ?? 7, 'sort_order' => 2],
            ['name' => 'Indian Cuisine', 'slug' => 'indian-cuisine', 'store_category_id' => $foodCatering->id ?? 7, 'sort_order' => 3],
            ['name' => 'Western Food', 'slug' => 'western-food', 'store_category_id' => $foodCatering->id ?? 7, 'sort_order' => 4],
            ['name' => 'Fast Food', 'slug' => 'fast-food', 'store_category_id' => $foodCatering->id ?? 7, 'sort_order' => 5],
            ['name' => 'Beverages', 'slug' => 'beverages', 'store_category_id' => $foodCatering->id ?? 7, 'sort_order' => 6],
            ['name' => 'Desserts', 'slug' => 'desserts', 'store_category_id' => $foodCatering->id ?? 7, 'sort_order' => 7],
            ['name' => 'Catering Services', 'slug' => 'catering-services', 'store_category_id' => $foodCatering->id ?? 7, 'sort_order' => 8],
        ];

        foreach ($foodCategories as $category) {
            ProductCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
