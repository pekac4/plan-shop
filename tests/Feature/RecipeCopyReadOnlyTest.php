<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
