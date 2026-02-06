<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders read-only recipe details', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create([
        'title' => 'Notebook Pancakes',
    ]);

    $response = $this->actingAs($user)->get(route('recipes.show', $recipe));

    $response
        ->assertOk()
        ->assertSee('Notebook Pancakes')
        ->assertDontSee('name="title"');
});
