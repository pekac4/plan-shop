<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests can switch the locale and store it in session', function () {
    $this->from('/')
        ->post(route('locale.switch'), ['locale' => 'sr'])
        ->assertRedirect('/');

    $this->assertSame('sr', session('locale'));
});

test('signed in users persist their locale preference', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)
        ->from('/dashboard')
        ->post(route('locale.switch'), ['locale' => 'sr'])
        ->assertRedirect('/dashboard');

    $user->refresh();

    expect($user->locale)->toBe('sr');
    expect(session('locale'))->toBe('sr');
});
