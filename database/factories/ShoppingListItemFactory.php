<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShoppingListItem>
 */
class ShoppingListItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'range_start' => fake()->date(),
            'range_end' => fake()->date(),
            'name' => fake()->words(2, true),
            'unit' => fake()->optional()->randomElement(['g', 'tbsp', 'tsp', 'cup']),
            'quantity' => fake()->optional()->randomFloat(2, 0.5, 10),
            'checked_at' => null,
        ];
    }
}
