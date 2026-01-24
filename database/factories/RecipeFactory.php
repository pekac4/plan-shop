<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
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
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'servings' => fake()->numberBetween(1, 8),
            'prep_time_minutes' => fake()->optional()->numberBetween(5, 60),
            'cook_time_minutes' => fake()->optional()->numberBetween(5, 120),
            'instructions' => fake()->paragraphs(3, true),
            'source_url' => fake()->optional()->url(),
            'is_public' => false,
        ];
    }
}
