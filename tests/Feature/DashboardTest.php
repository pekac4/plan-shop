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

    ShoppingListItem::factory()->for($user)->create([
        'range_start' => '2025-11-28',
        'range_end' => '2025-12-03',
        'price' => 5.0,
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

    $starChefOne = User::factory()->create(['name' => 'Ava Chef']);
    $starChefTwo = User::factory()->create(['name' => 'Ben Chef']);

    $starRecipeOne = Recipe::factory()->for($starChefOne)->create([
        'title' => 'Star Salad',
        'is_public' => true,
    ]);

    $starRecipeTwo = Recipe::factory()->for($starChefTwo)->create([
        'title' => 'Star Soup',
        'is_public' => true,
    ]);

    Recipe::factory()->for($user)->create([
        'original_recipe_id' => $starRecipeOne->id,
    ]);

    Recipe::factory()->for($otherUser)->create([
        'original_recipe_id' => $starRecipeOne->id,
    ]);

    Recipe::factory()->for($starChefOne)->create([
        'original_recipe_id' => $starRecipeOne->id,
    ]);

    Recipe::factory()->for($user)->create([
        'original_recipe_id' => $starRecipeTwo->id,
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Pasta');
    $response->assertSee('Egg');
    $response->assertSee('4 pcs');
    $response->assertSee('Public Stew');
    $response->assertSee($otherUser->name);
    $response->assertSee('Ava Chef');
    $response->assertSee('Ben Chef');
    $response->assertSee('Star Salad');
    $response->assertSee('Star Soup');
    $starOneCount = Recipe::query()
        ->selectSub(function ($subQuery): void {
            $subQuery->from('recipes as copies')
                ->selectRaw('count(distinct copies.user_id)')
                ->whereColumn('copies.original_recipe_id', 'recipes.id')
                ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
        }, 'saves_count')
        ->whereKey($starRecipeOne->id)
        ->value('saves_count');

    $starTwoCount = Recipe::query()
        ->selectSub(function ($subQuery): void {
            $subQuery->from('recipes as copies')
                ->selectRaw('count(distinct copies.user_id)')
                ->whereColumn('copies.original_recipe_id', 'recipes.id')
                ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
        }, 'saves_count')
        ->whereKey($starRecipeTwo->id)
        ->value('saves_count');

    expect((int) $starOneCount)->toBe(2);
    expect((int) $starTwoCount)->toBe(1);
    $response->assertSee('Approx. $25.5');
    $response->assertSee('md:col-span-3');
});
