<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('smokes the meal planner pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    visit([
        route('recipes.index'),
        route('meal-plan.index'),
        route('shopping-list.index'),
    ])->assertNoSmoke();
});
