<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows only owned recipes on the recipes index', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $owned = Recipe::factory()->for($user)->create([
        'title' => 'My Recipe',
        'is_public' => false,
    ]);

    Recipe::factory()->for($other)->create([
        'title' => 'Public Recipe',
        'is_public' => true,
    ]);

    $original = Recipe::factory()->for($other)->create([
        'title' => 'Original Recipe',
        'is_public' => true,
    ]);

    Recipe::factory()->for($user)->create([
        'title' => 'My Copy of Mine',
        'original_recipe_id' => $owned->id,
        'is_public' => true,
    ]);

    Recipe::factory()->for($user)->create([
        'title' => 'Copied Recipe',
        'original_recipe_id' => $original->id,
        'is_public' => true,
    ]);

    $this->actingAs($user)
        ->get(route('recipes.index'))
        ->assertOk()
        ->assertSee($owned->title)
        ->assertSee('Copied Recipe')
        ->assertDontSee('Public Recipe');
});
