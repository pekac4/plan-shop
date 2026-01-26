<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\View\View;

class KingOfRecipeController extends Controller
{
    public function __invoke(): View
    {
        $leader = User::query()
            ->select('users.id', 'users.name', 'users.avatar_path')
            ->selectSub(function ($subQuery): void {
                $subQuery->from('recipes as originals')
                    ->join('recipes as copies', function ($join): void {
                        $join->on('copies.original_recipe_id', '=', 'originals.id')
                            ->whereColumn('copies.user_id', '!=', 'originals.user_id');
                    })
                    ->whereColumn('originals.user_id', 'users.id')
                    ->whereNull('originals.original_recipe_id')
                    ->where('originals.is_public', true)
                    ->selectRaw('count(distinct copies.user_id)');
            }, 'stars')
            ->orderByDesc('stars')
            ->first();

        $leaderRecipes = collect();

        if ($leader) {
            $leaderRecipes = Recipe::query()
                ->whereNull('original_recipe_id')
                ->where('is_public', true)
                ->where('user_id', $leader->id)
                ->select('recipes.id', 'recipes.title', 'recipes.cover_image_path', 'recipes.cover_thumbnail_path')
                ->selectSub(function ($subQuery): void {
                    $subQuery->from('recipes as copies')
                        ->selectRaw('count(distinct copies.user_id)')
                        ->whereColumn('copies.original_recipe_id', 'recipes.id')
                        ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
                }, 'stars')
                ->orderByDesc('stars')
                ->limit(10)
                ->get();
        }

        return view('highlights.king-of-the-recipe', [
            'leader' => $leader,
            'leaderRecipes' => $leaderRecipes,
        ]);
    }
}
