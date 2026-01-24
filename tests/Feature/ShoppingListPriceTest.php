<?php

use App\Actions\ShoppingList\BuildShoppingList;
use App\Models\Ingredient;
use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('aggregates shopping list prices based on ingredient price and servings', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create();

    Ingredient::factory()->for($recipe)->create([
        'name' => 'Tomato',
        'quantity' => 2,
        'unit' => 'pcs',
        'price' => 1.5,
    ]);

    MealPlanEntry::factory()->forRecipe($recipe, servings: 3)->create([
        'date' => '2026-01-20',
        'meal' => 'dinner',
    ]);

    $items = app(BuildShoppingList::class)->handle(
        $user,
        CarbonImmutable::parse('2026-01-20'),
        CarbonImmutable::parse('2026-01-26'),
    );

    $tomatoes = collect($items)->firstWhere(fn (array $item) => $item['unit'] === 'pcs');

    expect($tomatoes['display_quantity'])->toBe('6')
        ->and($tomatoes['price'])->toBe('9');
});

it('calculates the total shopping list price for the selected range', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create();

    Ingredient::factory()->for($recipe)->create([
        'name' => 'Tomato',
        'quantity' => 2,
        'unit' => 'pcs',
        'price' => 1.5,
    ]);

    MealPlanEntry::factory()->forRecipe($recipe, servings: 3)->create([
        'date' => '2026-01-20',
        'meal' => 'dinner',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::shopping-list.index')
        ->set('rangeStart', '2026-01-20')
        ->set('rangeEnd', '2026-01-26')
        ->call('generate')
        ->assertSet('totalPrice', '9.00');
});
