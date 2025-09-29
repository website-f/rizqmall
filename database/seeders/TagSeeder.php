<?php

namespace Database\Seeders;

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
        $initialTags = ['new', 'best-seller', 'clearance', 'local-stock'];

        foreach ($initialTags as $tag) {
            // Only insert if the tag doesn't already exist
            if (!DB::table('tags')->where('name', $tag)->exists()) {
                DB::table('tags')->insert([
                    'name' => $tag,
                    'slug' => Str::slug($tag),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
