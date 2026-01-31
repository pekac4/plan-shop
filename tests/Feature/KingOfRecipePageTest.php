<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the king of the recipe page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('highlights.king-of-recipe'))
        ->assertSuccessful()
        ->assertSee('King of the Recipe');
});
