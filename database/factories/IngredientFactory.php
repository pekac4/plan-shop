<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'name' => fake()->words(2, true),
            'quantity' => fake()->optional()->randomFloat(2, 0.1, 10),
            'unit' => fake()->optional()->randomElement(['g', 'kg', 'tbsp', 'tsp', 'cup', 'ml']),
            'note' => fake()->optional()->words(2, true),
            'price' => fake()->optional()->randomFloat(2, 0.1, 20),
        ];
    }
}
