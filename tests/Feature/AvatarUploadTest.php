<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows users to upload an avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::settings.profile')
        ->set('avatar', UploadedFile::fake()->image('avatar.png', 400, 400))
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();

    Storage::disk('public')->assertExists($user->avatar_path);
});

it('rejects svg avatar uploads', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::settings.profile')
        ->set('avatar', UploadedFile::fake()->create('avatar.svg', 10, 'image/svg+xml'))
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasErrors(['avatar']);

    $user->refresh();

    expect($user->avatar_path)->toBeNull();
});
