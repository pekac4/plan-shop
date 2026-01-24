<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows owners to manage their recipes', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create();

    expect($user->can('view', $recipe))->toBeTrue()
        ->and($user->can('update', $recipe))->toBeTrue()
        ->and($user->can('delete', $recipe))->toBeTrue()
        ->and($user->can('duplicate', $recipe))->toBeTrue();
});

it('prevents other users from modifying recipes', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $recipe = Recipe::factory()->for($owner)->create();

    expect($otherUser->can('update', $recipe))->toBeFalse()
        ->and($otherUser->can('delete', $recipe))->toBeFalse()
        ->and($otherUser->can('duplicate', $recipe))->toBeFalse();
});

it('allows viewing public recipes but blocks private ones', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $publicRecipe = Recipe::factory()->for($owner)->create(['is_public' => true]);
    $privateRecipe = Recipe::factory()->for($owner)->create(['is_public' => false]);

    expect($otherUser->can('view', $publicRecipe))->toBeTrue()
        ->and($otherUser->can('view', $privateRecipe))->toBeFalse();
});
