<?php

use App\Models\CustomShoppingItem;
use App\Models\Ingredient;
use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Models\ShoppingListCustomItem;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows last month summaries', function () {
    CarbonImmutable::setTestNow('2026-01-24');

    $user = User::factory()->create();
    $this->actingAs($user);

    $recipeOne = Recipe::factory()->for($user)->create(['title' => 'Pasta']);
    $recipeTwo = Recipe::factory()->for($user)->create(['title' => 'Salad']);

    Ingredient::factory()->for($recipeOne)->create([
        'name' => 'Egg',
        'quantity' => 2,
        'unit' => 'pcs',
        'price' => 1.0,
    ]);

    Ingredient::factory()->for($recipeTwo)->create([
        'name' => 'Lettuce',
        'quantity' => 1,
        'unit' => 'pcs',
        'price' => 1.0,
    ]);

    MealPlanEntry::factory()->forRecipe($recipeOne, servings: 1)->create([
        'date' => '2025-12-10',
        'meal' => 'dinner',
    ]);

    MealPlanEntry::factory()->forRecipe($recipeOne, servings: 1)->create([
        'date' => '2025-12-12',
        'meal' => 'lunch',
    ]);

    MealPlanEntry::factory()->forRecipe($recipeTwo, servings: 1)->create([
        'date' => '2025-12-18',
        'meal' => 'lunch',
    ]);

    ShoppingListItem::factory()->for($user)->create([
        'range_start' => '2025-12-02',
        'range_end' => '2025-12-08',
        'price' => 12.5,
    ]);

    $customItem = CustomShoppingItem::factory()->for($user)->create([
        'name' => 'Coffee',
        'price' => 4.0,
    ]);

    ShoppingListCustomItem::factory()->for($customItem, 'customItem')->for($user)->create([
        'range_start' => '2025-12-09',
        'range_end' => '2025-12-15',
        'price' => 8.0,
    ]);

    $otherUser = User::factory()->create();
    $publicRecipe = Recipe::factory()->for($otherUser)->create([
        'title' => 'Public Stew',
        'is_public' => true,
    ]);

    Ingredient::factory()->for($publicRecipe)->create([
        'name' => 'Carrot',
        'quantity' => 1,
        'unit' => 'pcs',
        'price' => 1.0,
    ]);

    MealPlanEntry::factory()->forRecipe($publicRecipe, servings: 1)->create([
        'date' => '2025-12-20',
        'meal' => 'dinner',
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Pasta');
    $response->assertSee('Egg');
    $response->assertSee('Public Stew');
    $response->assertSee('Approx. $20.5');
});
