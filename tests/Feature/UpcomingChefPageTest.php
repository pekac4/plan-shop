<?php

use App\Models\User;

it('shows the upcoming chef page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('highlights.upcoming-chef'))
        ->assertSuccessful()
        ->assertSee('Upcoming Chef');
});
