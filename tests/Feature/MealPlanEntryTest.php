<?php

use App\Models\MealPlanEntry;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('enforces unique meal plan entries per user date and meal', function () {
    $user = User::factory()->create();

    MealPlanEntry::factory()->create([
        'user_id' => $user->id,
        'date' => '2026-01-20',
        'meal' => 'dinner',
    ]);

    expect(fn () => MealPlanEntry::factory()->create([
        'user_id' => $user->id,
        'date' => '2026-01-20',
        'meal' => 'dinner',
    ]))->toThrow(QueryException::class);
});
