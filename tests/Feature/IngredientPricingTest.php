<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('reuses ingredient price when omitted', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create();

    Ingredient::factory()->for($recipe)->create([
        'name' => 'Tomato',
        'price' => 2.5,
    ]);

    actingAs($user);

    Livewire::test('pages::recipes.create')
        ->set('title', 'Fresh Salad')
        ->set('instructions', 'Mix and serve.')
        ->set('ingredients', [
            [
                'name' => 'Tomato',
                'quantity' => 2,
                'unit' => 'pcs',
                'note' => '',
                'price' => null,
            ],
        ])
        ->call('save');

    $createdRecipe = Recipe::query()->where('user_id', $user->id)->latest('id')->first();

    expect($createdRecipe)->not()->toBeNull();

    assertDatabaseHas('ingredients', [
        'recipe_id' => $createdRecipe->id,
        'name' => 'Tomato',
        'price' => 2.5,
    ]);
});

it('defaults ingredient price to zero when no prior price exists', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test('pages::recipes.create')
        ->set('title', 'Plain Rice')
        ->set('instructions', 'Cook.')
        ->set('ingredients', [
            [
                'name' => 'Rice',
                'quantity' => 1,
                'unit' => 'cup',
                'note' => '',
                'price' => null,
            ],
        ])
        ->call('save');

    $createdRecipe = Recipe::query()->where('user_id', $user->id)->latest('id')->first();

    expect($createdRecipe)->not()->toBeNull();

    assertDatabaseHas('ingredients', [
        'recipe_id' => $createdRecipe->id,
        'name' => 'Rice',
        'price' => 0,
    ]);

    assertDatabaseMissing('ingredients', [
        'recipe_id' => $createdRecipe->id,
        'name' => 'Rice',
        'price' => null,
    ]);
});

it('calculates approximate recipe price from ingredient prices', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create();

    Ingredient::factory()->for($recipe)->create([
        'name' => 'Carrot',
        'quantity' => 2,
        'unit' => 'pcs',
        'price' => 1.25,
    ]);

    Ingredient::factory()->for($recipe)->create([
        'name' => 'Salt',
        'quantity' => null,
        'unit' => 'tsp',
        'price' => 0.5,
    ]);

    $recipe->load('ingredients');

    expect($recipe->approximate_price)->toBe(3.0);
});
