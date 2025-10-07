<?php

namespace Database\Seeders;

use App\Models\VariantType;
use App\Models\AttributeOption;
use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VariantSeeder extends Seeder
{
    public function run()
    {
         $variantTypes = [
            [
                'name' => 'Size',
                'slug' => 'size',
                'display_type' => 'button',
                'has_image' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Color',
                'slug' => 'color',
                'display_type' => 'color_swatch',
                'has_image' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Material',
                'slug' => 'material',
                'display_type' => 'button',
                'has_image' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Style',
                'slug' => 'style',
                'display_type' => 'button',
                'has_image' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Set',
                'slug' => 'set',
                'display_type' => 'button',
                'has_image' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Package',
                'slug' => 'package',
                'display_type' => 'button',
                'has_image' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Flavor',
                'slug' => 'flavor',
                'display_type' => 'button',
                'has_image' => false,
                'sort_order' => 7,
            ],
        ];

        foreach ($variantTypes as $type) {
            VariantType::create($type);
        }
    }
}
