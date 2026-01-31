<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the chef of the month page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('highlights.chef-of-month'))
        ->assertSuccessful()
        ->assertSee('Chef of the Month');
});
