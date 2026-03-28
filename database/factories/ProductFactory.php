<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'category_id' => Category::factory(),
            'brand_id'    => Brand::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name),
            'sku'         => strtoupper(Str::random(8)),
            'description' => fake()->paragraph(),
            'price'       => fake()->randomFloat(2, 10, 1000),
            'is_active'   => true,
        ];
    }
}
