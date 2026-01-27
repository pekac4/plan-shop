<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Models\ShoppingListCustomItem;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $userId = Auth::id();
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $monthStartDate = $monthStart->toDateString();
        $monthEndDate = $monthEnd->toDateString();

        $topRecipes = MealPlanEntry::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$monthStartDate, $monthEndDate])
            ->whereNotNull('recipe_id')
            ->select('recipe_id')
            ->selectRaw('count(*) as uses')
            ->groupBy('recipe_id')
            ->orderByDesc('uses')
            ->with('recipe:id,title')
            ->limit(5)
            ->get();

        $topIngredients = Ingredient::query()
            ->select('ingredients.name', 'ingredients.unit')
            ->selectRaw('count(*) as uses')
            ->selectRaw('sum(coalesce(ingredients.quantity, 1) * meal_plan_entries.servings) as total_quantity')
            ->join('recipes', 'recipes.id', '=', 'ingredients.recipe_id')
            ->join('meal_plan_entries', 'meal_plan_entries.recipe_id', '=', 'recipes.id')
            ->where('meal_plan_entries.user_id', $userId)
            ->whereBetween('meal_plan_entries.date', [$monthStartDate, $monthEndDate])
            ->groupBy('ingredients.name', 'ingredients.unit')
            ->orderByDesc('uses')
            ->limit(5)
            ->get()
            ->map(function ($ingredient) {
                $quantity = $ingredient->total_quantity
                    ? rtrim(rtrim(number_format((float) $ingredient->total_quantity, 2, '.', ''), '0'), '.')
                    : null;

                $ingredient->display_quantity = $quantity;

                return $ingredient;
            });

        $shoppingTotal = (float) ShoppingListItem::query()
            ->where('user_id', $userId)
            ->where('range_end', '>=', $monthStartDate)
            ->where('range_start', '<=', $monthEndDate)
            ->sum('price');

        $customTotal = (float) ShoppingListCustomItem::query()
            ->where('user_id', $userId)
            ->where('range_end', '>=', $monthStartDate)
            ->where('range_start', '<=', $monthEndDate)
            ->sum('price');

        $monthlyTotal = $shoppingTotal + $customTotal;
        $displayTotal = rtrim(rtrim(number_format($monthlyTotal, 2, '.', ''), '0'), '.');
        $monthLabel = $monthStart->format('F Y');
        $currencySymbol = config('app.currency_symbol', '$');

        $topCommunityRecipes = MealPlanEntry::query()
            ->whereBetween('date', [$monthStartDate, $monthEndDate])
            ->whereNotNull('recipe_id')
            ->whereHas('recipe', function ($query) use ($userId): void {
                $query->where('is_public', true)
                    ->where('user_id', '!=', $userId);
            })
            ->select('recipe_id')
            ->selectRaw('count(*) as uses')
            ->groupBy('recipe_id')
            ->orderByDesc('uses')
            ->with([
                'recipe' => function ($query): void {
                    $query->select('id', 'title', 'is_public', 'user_id', 'cover_image_path', 'cover_thumbnail_path')
                        ->selectSub(function ($subQuery): void {
                            $subQuery->from('recipes as copies')
                                ->selectRaw('count(distinct copies.user_id)')
                                ->whereColumn('copies.original_recipe_id', 'recipes.id')
                                ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
                        }, 'saves_count')
                        ->with('ingredients:id,recipe_id,name');
                },
                'recipe.user:id,name',
            ])
            ->limit(5)
            ->get();

        $communityRecipeIds = $topCommunityRecipes->pluck('recipe_id')->filter()->unique();

        $ownedOriginals = Recipe::query()
            ->where('user_id', $userId)
            ->whereNotNull('original_recipe_id')
            ->whereIn('original_recipe_id', $communityRecipeIds)
            ->pluck('id', 'original_recipe_id');

        $topSavedUsers = User::query()
            ->select('users.id', 'users.name', 'users.avatar_path')
            ->selectRaw('count(copies.id) as saves_count')
            ->join('recipes as originals', 'originals.user_id', '=', 'users.id')
            ->leftJoin('recipes as copies', function ($join): void {
                $join->on('copies.original_recipe_id', '=', 'originals.id')
                    ->whereColumn('copies.user_id', '!=', 'originals.user_id');
            })
            ->whereNull('originals.original_recipe_id')
            ->where('originals.is_public', true)
            ->groupBy('users.id', 'users.name', 'users.avatar_path')
            ->orderByDesc('saves_count')
            ->limit(5)
            ->get();

        $topSavedRecipes = Recipe::query()
            ->whereNull('original_recipe_id')
            ->where('is_public', true)
            ->select('id', 'title', 'cover_image_path', 'cover_thumbnail_path')
            ->selectSub(function ($subQuery): void {
                $subQuery->from('recipes as copies')
                    ->selectRaw('count(distinct copies.user_id)')
                    ->whereColumn('copies.original_recipe_id', 'recipes.id')
                    ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
            }, 'saves_count')
            ->orderByDesc('saves_count')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'topRecipes' => $topRecipes,
            'topIngredients' => $topIngredients,
            'displayTotal' => $displayTotal,
            'currencySymbol' => $currencySymbol,
            'monthLabel' => $monthLabel,
            'ownedOriginals' => $ownedOriginals,
            'topCommunityRecipes' => $topCommunityRecipes,
            'topSavedUsers' => $topSavedUsers,
            'topSavedRecipes' => $topSavedRecipes,
        ]);
    }
}
