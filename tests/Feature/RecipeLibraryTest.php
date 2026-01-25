<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows users to add a public recipe to their library', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();

    $recipe = Recipe::factory()->for($owner)->create([
        'title' => 'Public Soup',
        'is_public' => true,
    ]);

    Ingredient::factory()->for($recipe)->create([
        'name' => 'Carrot',
        'quantity' => 2,
        'unit' => 'pcs',
        'price' => 1.2,
    ]);

    $this->actingAs($user)
        ->post(route('recipes.add-to-library', $recipe))
        ->assertRedirect();

    $copy = Recipe::query()
        ->where('user_id', $user->id)
        ->where('title', 'Public Soup')
        ->first();

    expect($copy)->not()->toBeNull();
    expect($copy->original_recipe_id)->toBe($recipe->id);
    expect($copy->is_public)->toBeTrue();

    $this->actingAs($user)
        ->post(route('recipes.add-to-library', $recipe))
        ->assertRedirect();

    $copies = Recipe::query()
        ->where('user_id', $user->id)
        ->where('original_recipe_id', $recipe->id)
        ->count();

    expect($copies)->toBe(1);

    $this->assertDatabaseHas('ingredients', [
        'recipe_id' => $copy->id,
        'name' => 'Carrot',
        'price' => 1.2,
    ]);
});

it('does not add a recipe to the library when it belongs to the same user', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create([
        'title' => 'My Soup',
        'is_public' => true,
    ]);

    $this->actingAs($user)
        ->post(route('recipes.add-to-library', $recipe))
        ->assertRedirect();

    $copyExists = Recipe::query()
        ->where('user_id', $user->id)
        ->where('original_recipe_id', $recipe->id)
        ->exists();

    expect($copyExists)->toBeFalse();
});
