<?php

namespace App;

use App\Models\Recipe;
use Carbon\CarbonImmutable;

class HighlightRecipeSelector
{
    public function chefOfMonth(CarbonImmutable $monthStart, CarbonImmutable $monthEnd): ?Recipe
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

        return $fallback;
    }
}
