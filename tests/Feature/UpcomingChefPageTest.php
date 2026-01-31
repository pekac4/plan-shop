<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the upcoming chef page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('highlights.upcoming-chef'))
        ->assertSuccessful()
        ->assertSee('Upcoming Chef');
});
