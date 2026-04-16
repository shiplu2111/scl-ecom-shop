<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'children' => [
                    'Laptops', 'Smartphones', 'Accessories', 'Tablets'
                ]
            ],
            [
                'name' => 'Fashion',
                'children' => [
                    'Men\'s Clothing', 'Women\'s Clothing', 'Footwear', 'Watches'
                ]
            ],
            [
                'name' => 'Home Appliances',
                'children' => [
                    'Refrigerators', 'Washing Machines', 'Air Conditioners', 'Kitchenware'
                ]
            ],
            [
                'name' => 'Home Decor',
                'children' => [
                    'Furniture', 'Lighting', 'Curtains', 'Wall Art'
                ]
            ],
            [
                'name' => 'Health & Beauty',
                'children' => [
                    'Skincare', 'Haircare', 'Makeup', 'Fragrances'
                ]
            ],
        ];

        foreach ($categories as $catData) {
            $parent = Category::create([
                'name' => $catData['name'],
                'slug' => Str::slug($catData['name']),
            ]);

            if (isset($catData['children'])) {
                foreach ($catData['children'] as $childName) {
                    Category::create([
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'slug' => Str::slug($childName),
                    ]);
                }
            }
        }
    }
}
