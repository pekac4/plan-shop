<?php

use App\Actions\ShoppingList\BuildShoppingList;
use App\Models\Ingredient;
use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('aggregates shopping list items by name and unit', function () {
    $user = User::factory()->create();

    $recipeOne = Recipe::factory()->for($user)->create();
    $recipeTwo = Recipe::factory()->for($user)->create();

    Ingredient::factory()->for($recipeOne)->create([
        'name' => 'Tomato',
        'quantity' => 2,
        'unit' => 'pcs',
        'price' => 1.0,
    ]);

    Ingredient::factory()->for($recipeTwo)->create([
        'name' => 'tomato ',
        'quantity' => 1.5,
        'unit' => 'pcs',
        'price' => 1.0,
    ]);

    Ingredient::factory()->for($recipeTwo)->create([
        'name' => 'Tomato',
        'quantity' => null,
        'unit' => 'pcs',
        'price' => 1.0,
    ]);

    Ingredient::factory()->for($recipeTwo)->create([
        'name' => 'Tomato',
        'quantity' => 2,
        'unit' => 'g',
        'price' => 1.0,
    ]);

    MealPlanEntry::factory()->forRecipe($recipeOne, servings: 2)->create([
        'date' => '2026-01-20',
        'meal' => 'dinner',
    ]);

    MealPlanEntry::factory()->forRecipe($recipeTwo, servings: 1)->create([
        'date' => '2026-01-21',
        'meal' => 'lunch',
    ]);

    MealPlanEntry::factory()->create([
        'user_id' => $user->id,
        'date' => '2026-01-22',
        'meal' => 'snack',
        'recipe_id' => null,
        'custom_title' => 'Custom',
        'servings' => 1,
    ]);

    $items = app(BuildShoppingList::class)->handle(
        $user,
        CarbonImmutable::parse('2026-01-20'),
        CarbonImmutable::parse('2026-01-26'),
    );

    expect($items)->toHaveCount(3);

    $tomatoes = collect($items)->firstWhere(fn (array $item) => $item['unit'] === 'pcs' && $item['quantity'] !== null);
    $tomatoesNull = collect($items)->firstWhere(fn (array $item) => $item['unit'] === 'pcs' && $item['quantity'] === null);
    $tomatoesGrams = collect($items)->firstWhere(fn (array $item) => $item['unit'] === 'g');

    expect($tomatoes['display_quantity'])->toBe('5.5')
        ->and($tomatoes['price'])->toBe('5.5')
        ->and($tomatoes['source_recipes_count'])->toBe(2);

    expect($tomatoesNull['display_quantity'])->toBeNull()
        ->and($tomatoesNull['price'])->toBe('1');

    expect($tomatoesGrams['display_quantity'])->toBe('2')
        ->and($tomatoesGrams['price'])->toBe('2')
        ->and($tomatoesGrams['source_recipes_count'])->toBe(1);
});
