<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'code' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'price' => fake()->randomFloat(2, 10, 500),
            'tax_percentage' => fake()->randomElement(['0.00', '5.00', '12.00', '18.00']),
            'stock_on_hand' => fake()->numberBetween(5, 80),
        ];
    }
}
