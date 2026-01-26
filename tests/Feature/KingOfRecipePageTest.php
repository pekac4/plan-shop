<?php

use App\Models\User;

it('shows the king of the recipe page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('highlights.king-of-recipe'))
        ->assertSuccessful()
        ->assertSee('King of the Recipe');
});
