<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class ChefOfMonthController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

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

        if (! $recipe) {
            $recipe = Recipe::query()
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
        }

        return view('highlights.chef-of-the-month', [
            'recipe' => $recipe,
            'monthLabel' => $monthStart->format('F Y'),
        ]);
    }
}
