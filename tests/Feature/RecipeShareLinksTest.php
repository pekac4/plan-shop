<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows recipe share links on the detail view', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->for($user)->create([
        'title' => 'Herb Pasta',
    ]);

    $response = $this->actingAs($user)->get(route('recipes.edit', $recipe));

    $response->assertOk();
    $response->assertSee('Share this recipe');
    $response->assertSee('x.com/intent/tweet');
    $response->assertSee('facebook.com/sharer/sharer.php');
    $response->assertSee('instagram.com/?url=');
    $response->assertSee('mail.google.com/mail/?view=cm');
});
