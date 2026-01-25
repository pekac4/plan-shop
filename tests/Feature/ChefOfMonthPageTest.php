<?php

use App\Models\User;

it('shows the chef of the month page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('highlights.chef-of-month'))
        ->assertSuccessful()
        ->assertSee('Chef of the Month');
});
