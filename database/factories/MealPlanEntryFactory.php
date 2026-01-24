<?php

namespace Database\Factories;

use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MealPlanEntry>
 */
class MealPlanEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\MealPlanEntry>
     */
    protected $model = MealPlanEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->date(),
            'meal' => fake()->randomElement(MealPlanEntry::MEALS),
            'recipe_id' => null,
            'custom_title' => fake()->words(2, true),
            'servings' => fake()->numberBetween(1, 4),
        ];
    }

    public function forRecipe(Recipe $recipe, ?int $servings = null): self
    {
        return $this->state(function () use ($recipe, $servings): array {
            return [
                'user_id' => $recipe->user_id,
                'recipe_id' => $recipe->id,
                'custom_title' => null,
                'servings' => $servings ?? fake()->numberBetween(1, 4),
            ];
        });
    }
}
