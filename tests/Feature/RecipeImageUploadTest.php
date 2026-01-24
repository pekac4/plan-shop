<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('stores a recipe cover image and thumbnail', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::recipes.create')
        ->set('title', 'Veggie Bowl')
        ->set('instructions', 'Mix and serve.')
        ->set('coverImage', UploadedFile::fake()->image('cover.jpg', 1400, 900))
        ->set('ingredients', [
            [
                'name' => 'Tomato',
                'quantity' => 1,
                'unit' => 'pc',
                'note' => '',
                'price' => 0.5,
            ],
        ])
        ->call('save');

    $recipe = $user->recipes()->latest('id')->first();

    expect($recipe)->not()->toBeNull()
        ->and($recipe->cover_image_path)->not->toBeNull()
        ->and($recipe->cover_thumbnail_path)->not->toBeNull();

    Storage::disk('public')->assertExists($recipe->cover_image_path);
    Storage::disk('public')->assertExists($recipe->cover_thumbnail_path);
});
