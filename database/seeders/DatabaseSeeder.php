<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            TagSeeder::class, 
            VariantSeeder::class,
            ProductCategorySeeder::class,
            // Add other seeders here (e.g., UserSeeder::class, StoreSeeder::class)
        ]);
    }
}
