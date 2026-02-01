<?php

namespace App;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;

class HighlightRecipeSelector
{
    public function chefOfMonth(CarbonImmutable $monthStart, CarbonImmutable $monthEnd): Recipe
    {
        $recipe = Recipe::query()
            ->whereNull('original_recipe_id')
            ->where('is_public', true)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->select('recipes.*')
            ->selectSub(function ($subQuery): void {
                $subQuery->from('recipes as copies')
                    ->selectRaw('count(distinct copies.user_id)')
                    ->whereColumn('copies.original_recipe_id', 'recipes.id')
                    ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
            }, 'stars')
            ->with([
                'user:id,name,avatar_path',
                'ingredients:id,recipe_id,name,quantity,unit,note',
            ])
            ->orderByDesc('stars')
            ->latest('created_at')
            ->first();

        if ($recipe) {
            return $recipe;
        }

        $fallback = Recipe::query()
            ->whereNull('original_recipe_id')
            ->where('is_public', true)
            ->select('recipes.*')
            ->selectSub(function ($subQuery): void {
                $subQuery->from('recipes as copies')
                    ->selectRaw('count(distinct copies.user_id)')
                    ->whereColumn('copies.original_recipe_id', 'recipes.id')
                    ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
            }, 'stars')
            ->with([
                'user:id,name,avatar_path',
                'ingredients:id,recipe_id,name,quantity,unit,note',
            ])
            ->orderByDesc('stars')
            ->latest('created_at')
            ->first();

        if ($fallback) {
            return $fallback;
        }

        return $this->createDemoRecipe($monthStart);
    }

    private function createDemoRecipe(CarbonImmutable $monthStart): Recipe
    {
        $author = User::query()
            ->where('email', 'maria_k@example.com')
            ->first();

        if (! $author) {
            $author = User::factory()->create([
                'name' => 'Maria K',
                'email' => 'maria_k@example.com',
            ]);
        }

        $recipe = Recipe::factory()->for($author)->create([
            'title' => 'Roasted Veggie Bowl with Lemon Tahini',
            'description' => 'Fresh, fast, and perfect for busy weeks — ready in 25 minutes.',
            'instructions' => 'Roast veggies, whisk tahini sauce, and assemble bowls.',
            'prep_time_minutes' => 10,
            'cook_time_minutes' => 15,
            'servings' => 2,
            'is_public' => true,
            'created_at' => $monthStart->addDays(4),
        ]);

        Ingredient::factory()->for($recipe)->create([
            'name' => 'Zucchini',
            'quantity' => 2,
            'unit' => 'pcs',
            'price' => 1.0,
        ]);

        return $recipe->load([
            'user:id,name,avatar_path',
            'ingredients:id,recipe_id,name,quantity,unit,note',
        ]);
    }
}
