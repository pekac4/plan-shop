<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('switches locale from the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit(route('dashboard'))->on()->macbook16()
        ->assertSee('Dashboard');

    $page->click('@locale-sr')
        ->waitForText('Kontrolna tabla');

    $page->click('@locale-en')
        ->waitForText('Dashboard');
});
