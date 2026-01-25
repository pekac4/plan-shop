<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class UpcomingChefController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $leader = User::query()
            ->select('users.id', 'users.name', 'users.avatar_path')
            ->selectSub(function ($subQuery) use ($monthStart, $monthEnd): void {
                $subQuery->from('recipes as originals')
                    ->join('recipes as copies', function ($join): void {
                        $join->on('copies.original_recipe_id', '=', 'originals.id')
                            ->whereColumn('copies.user_id', '!=', 'originals.user_id');
                    })
                    ->whereColumn('originals.user_id', 'users.id')
                    ->whereNull('originals.original_recipe_id')
                    ->where('originals.is_public', true)
                    ->whereBetween('copies.created_at', [$monthStart, $monthEnd])
                    ->selectRaw('count(distinct originals.id)');
            }, 'recipes_count')
            ->orderByDesc('recipes_count')
            ->first();

        $leaderRecipes = collect();

        if ($leader) {
            $leaderRecipes = Recipe::query()
                ->whereNull('original_recipe_id')
                ->where('is_public', true)
                ->where('user_id', $leader->id)
                ->select('recipes.id', 'recipes.title', 'recipes.cover_image_path', 'recipes.cover_thumbnail_path')
                ->selectSub(function ($subQuery) use ($monthStart, $monthEnd): void {
                    $subQuery->from('recipes as copies')
                        ->selectRaw('count(distinct copies.user_id)')
                        ->whereColumn('copies.original_recipe_id', 'recipes.id')
                        ->whereColumn('copies.user_id', '!=', 'recipes.user_id')
                        ->whereBetween('copies.created_at', [$monthStart, $monthEnd]);
                }, 'last_month_copies')
                ->whereHas('copies', function ($query) use ($monthStart, $monthEnd): void {
                    $query->whereBetween('created_at', [$monthStart, $monthEnd]);
                })
                ->orderByDesc('last_month_copies')
                ->limit(10)
                ->get();
        }

        return view('highlights.upcoming-chef', [
            'leader' => $leader,
            'leaderRecipes' => $leaderRecipes,
            'monthLabel' => $monthStart->format('F Y'),
        ]);
    }
}
