<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

it('redirects to google for authentication', function () {
    Socialite::fake('google');

    $this->get(route('auth.google.redirect'))
        ->assertRedirect();
});

it('creates or updates a user from google and logs them in', function () {
    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-123',
        'name' => 'Google User',
        'email' => 'google.user@example.com',
        'avatar' => null,
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'email' => 'google.user@example.com',
        'google_id' => 'google-123',
    ]);

    expect(User::where('email', 'google.user@example.com')->value('avatar_path'))->toBeNull();
});

it('redirects google logins to the intended url when present', function () {
    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-125',
        'name' => 'Google User',
        'email' => 'google.intended@example.com',
        'avatar' => null,
    ]));

    $this->withSession(['url.intended' => route('recipes.index')])
        ->get(route('auth.google.callback'))
        ->assertRedirect(route('recipes.index'));
});

it('does not fetch avatars from non-google hosts', function () {
    Http::fake();

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-124',
        'name' => 'Google User',
        'email' => 'google.blocked@example.com',
        'avatar' => 'https://evil.test/avatar.png',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard'));

    Http::assertNothingSent();

    expect(User::where('email', 'google.blocked@example.com')->value('avatar_path'))->toBeNull();
});
