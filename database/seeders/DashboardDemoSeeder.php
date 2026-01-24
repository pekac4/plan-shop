<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DashboardDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $users = User::query()
            ->select('id')
            ->get();

        if ($users->count() === 1) {
            $users->push(User::factory()->create());
        }

        $primaryUserId = $users->first()?->id;

        $users->each(function (User $user) use ($monthStart, $monthEnd, $primaryUserId): void {
            $hasEntries = MealPlanEntry::query()
                ->where('user_id', $user->id)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->exists();

            if ($hasEntries) {
                if ($primaryUserId && $user->id !== $primaryUserId) {
                    $hasPublicUsage = MealPlanEntry::query()
                        ->where('user_id', $user->id)
                        ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->whereNotNull('recipe_id')
                        ->whereHas('recipe', fn ($query) => $query->where('is_public', true))
                        ->exists();

                    if ($hasPublicUsage) {
                        return;
                    }
                } else {
                    return;
                }
            }

            $recipes = Recipe::factory()
                ->for($user)
                ->count(5)
                ->create([
                    'is_public' => $primaryUserId ? $user->id !== $primaryUserId : false,
                ]);

            foreach ($recipes as $recipe) {
                Ingredient::factory()
                    ->for($recipe)
                    ->count(2)
                    ->create([
                        'price' => fake()->randomFloat(2, 0.5, 5),
                    ]);
            }

            $meals = MealPlanEntry::MEALS;
            $recipeIndex = 0;
            $days = $monthStart->diffInDays($monthEnd) + 1;

            for ($offset = 0; $offset < $days; $offset++) {
                $date = $monthStart->addDays($offset)->toDateString();

                foreach ($meals as $meal) {
                    $recipe = $recipes[$recipeIndex % $recipes->count()];
                    $recipeIndex++;

                    MealPlanEntry::query()->updateOrCreate([
                        'user_id' => $user->id,
                        'date' => $date,
                        'meal' => $meal,
                    ], [
                        'recipe_id' => $recipe->id,
                        'custom_title' => null,
                        'servings' => fake()->numberBetween(1, 3),
                    ]);
                }
            }
        });

        $otherUser = $users->firstWhere(fn (User $user) => $user->id !== $primaryUserId);

        if ($otherUser) {
            $publicRecipe = Recipe::query()
                ->where('user_id', $otherUser->id)
                ->where('is_public', true)
                ->first();

            if ($publicRecipe) {
                Recipe::factory()
                    ->for($otherUser)
                    ->count(3)
                    ->create([
                        'original_recipe_id' => $publicRecipe->id,
                        'title' => 'Copy of '.$publicRecipe->title,
                        'is_public' => false,
                        'created_at' => $monthStart->addDays(2),
                    ]);
            }
        }
    }
}
