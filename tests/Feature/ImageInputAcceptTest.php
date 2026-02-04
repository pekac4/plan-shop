<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('limits image input accept types on recipe create and edit forms', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('recipes.create'))
        ->assertOk()
        ->assertSee('accept=".jpg,.jpeg,.png,.webp"', false);

    $this->actingAs($user)
        ->get(route('recipes.edit', $recipe))
        ->assertOk()
        ->assertSee('accept=".jpg,.jpeg,.png,.webp"', false);
});

it('limits image input accept types on profile settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('accept=".jpg,.jpeg,.png,.webp"', false);
});
