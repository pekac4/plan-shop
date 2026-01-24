<?php

namespace Database\Factories;

use App\Models\CustomShoppingItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShoppingListCustomItem>
 */
class ShoppingListCustomItemFactory extends Factory
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
            'custom_shopping_item_id' => CustomShoppingItem::factory(),
            'range_start' => fake()->date(),
            'range_end' => fake()->date(),
            'quantity' => fake()->optional()->randomFloat(2, 1, 5),
            'price' => fake()->randomFloat(2, 0, 20),
        ];
    }
}
