<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
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
            'Electronics' => [
                'Smartphones',
                'Laptops & Desktops',
                'Cameras & Photo',
                'Audio Devices',
            ],
            'Apparel & Fashion' => [
                'Men\'s Clothing',
                'Women\'s Clothing',
                'Footwear',
                'Accessories',
            ],
            'Home & Living' => [
                'Furniture',
                'Kitchenware',
                'Decorations',
                'Tools & Hardware',
            ],
        ];

        $parentId = [];

        // Insert Parent Categories
        foreach (array_keys($categories) as $parentName) {
            DB::table('categories')->insert([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'parent_id' => null,
                'description' => 'Products related to ' . $parentName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Store the ID of the newly inserted parent
            $parentId[$parentName] = DB::getPdo()->lastInsertId();
        }

        // Insert Child Categories and link them to their parents
        foreach ($categories as $parentName => $children) {
            foreach ($children as $childName) {
                DB::table('categories')->insert([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'parent_id' => $parentId[$parentName],
                    'description' => 'Subcategory under ' . $parentName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
