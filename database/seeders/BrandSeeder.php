<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Samsung', 'Apple', 'Sony', 'LG', 'Panasonic',
            'Nike', 'Adidas', 'Puma', 'Zara', 'H&M',
            'Dell', 'HP', 'Lenovo', 'Asus', 'Microsoft',
            'IKEA', 'L\'Oreal', 'NIVEA', 'Maybelline'
        ];

        foreach ($brands as $brandName) {
            Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName),
                'logo' => null, // Placeholder for external assets
            ]);
        }
    }
}
