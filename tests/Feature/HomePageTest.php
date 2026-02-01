<?php

use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the marketing hero and recipe of the month', function () {
    CarbonImmutable::setTestNow('2026-01-25');

    $author = User::factory()->create([
        'name' => 'Maria K',
        'email' => 'maria_k@example.com',
    ]);

    $recipe = Recipe::factory()->for($author)->create([
        'title' => 'Roasted Veggie Bowl with Lemon Tahini',
        'description' => 'Fresh, fast, and perfect for busy weeks — ready in 25 minutes.',
        'is_public' => true,
        'created_at' => CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth()->addDays(4),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Plan meals. Shop smarter. Cook better.');
    $response->assertSee('Recipe of the Month');
    $response->assertSee($recipe->title);
    $response->assertSee('@maria_k');
    $response->assertSee('Share the app');
    $response->assertSee('Share this recipe');
    $response->assertSee('x.com/intent/tweet');
    $response->assertSee('facebook.com/sharer/sharer.php');
    $response->assertSee('instagram.com/?url=');
    $response->assertSee('mail.google.com/mail/?view=cm');
});
