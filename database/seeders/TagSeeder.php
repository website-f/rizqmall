<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'New Arrival', 'slug' => 'new-arrival', 'color' => '#10b981'],
            ['name' => 'Best Seller', 'slug' => 'best-seller', 'color' => '#f59e0b'],
            ['name' => 'Featured', 'slug' => 'featured', 'color' => '#3b82f6'],
            ['name' => 'Limited Edition', 'slug' => 'limited-edition', 'color' => '#8b5cf6'],
            ['name' => 'Sale', 'slug' => 'sale', 'color' => '#ef4444'],
            ['name' => 'Organic', 'slug' => 'organic', 'color' => '#22c55e'],
            ['name' => 'Handmade', 'slug' => 'handmade', 'color' => '#f97316'],
            ['name' => 'Local', 'slug' => 'local', 'color' => '#06b6d4'],
            ['name' => 'Imported', 'slug' => 'imported', 'color' => '#6366f1'],
            ['name' => 'Premium', 'slug' => 'premium', 'color' => '#d97706'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
