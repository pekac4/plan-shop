<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders copied recipes as read-only', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();

    $recipe = Recipe::factory()->for($owner)->create([
        'title' => 'Public Soup',
        'is_public' => true,
    ]);

    $copy = Recipe::factory()->for($user)->create([
        'title' => 'Public Soup',
        'original_recipe_id' => $recipe->id,
        'is_public' => false,
    ]);

    $this->actingAs($user)
        ->get(route('recipes.edit', $copy))
        ->assertOk()
        ->assertSee('Viewing a copied recipe.')
        ->assertDontSee('Save Changes');
});

it('blocks save actions on copied recipes with a user-facing error', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();

    $recipe = Recipe::factory()->for($owner)->create([
        'title' => 'Public Soup',
        'is_public' => true,
    ]);

    $copy = Recipe::factory()->for($user)->create([
        'title' => 'Public Soup',
        'original_recipe_id' => $recipe->id,
        'is_public' => true,
    ]);

    Livewire::actingAs($user)
        ->test('pages::recipes.edit', ['recipe' => $copy])
        ->set('title', 'Attempted Update')
        ->set('instructions', 'Do not save.')
        ->set('servings', 2)
        ->set('ingredients', [
            [
                'name' => 'Tomato',
                'quantity' => 1,
                'unit' => 'pc',
                'note' => '',
                'price' => 0.5,
            ],
        ])
        ->call('save')
        ->assertHasErrors(['recipe']);

    expect($copy->fresh()->title)->toBe('Public Soup');
});

it('uses original recipe cover images for copies', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();

    $recipe = Recipe::factory()->for($owner)->create([
        'cover_image_path' => 'recipes/1/cover.jpg',
        'cover_thumbnail_path' => 'recipes/1/cover-thumb.jpg',
        'is_public' => true,
    ]);

    $copy = Recipe::factory()->for($user)->create([
        'original_recipe_id' => $recipe->id,
        'cover_image_path' => null,
        'cover_thumbnail_path' => null,
        'is_public' => true,
    ]);

    $copy->load('originalRecipe');

    expect($copy->cover_image_url)->toBe($recipe->cover_image_url)
        ->and($copy->cover_thumbnail_url)->toBe($recipe->cover_thumbnail_url);
});
