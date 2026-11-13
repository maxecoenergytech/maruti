<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(ProductCategory::cases()),
            'hsn_sac' => fake()->numerify('8471####'),
            'unit' => 'Pcs',
            'default_rate' => fake()->randomFloat(2, 500, 75000),
            'gst_rate' => 18.00,
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
