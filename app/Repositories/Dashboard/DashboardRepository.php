<?php

namespace App\Repositories\Dashboard;

use App\Models\Ingredient;
use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Models\ShoppingListCustomItem;
use App\Models\ShoppingListItem;
use App\Models\User;
use Closure;
use Illuminate\Support\Collection;

class DashboardRepository
{
    private const TOP_LIMIT = 5;

    /**
     * @param  array{0: string, 1: string}  $monthRange
     */
    public function topRecipes(User $user, array $monthRange): Collection
    {
        return MealPlanEntry::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', $monthRange)
            ->whereNotNull('recipe_id')
            ->select('recipe_id')
            ->selectRaw('count(*) as uses')
            ->groupBy('recipe_id')
            ->orderByDesc('uses')
            ->with('recipe:id,title')
            ->limit(self::TOP_LIMIT)
            ->get();
    }

    /**
     * @param  array{0: string, 1: string}  $monthRange
     */
    public function topIngredients(User $user, array $monthRange): Collection
    {
        return Ingredient::query()
            ->select('ingredients.name', 'ingredients.unit')
            ->selectRaw('count(*) as uses')
            ->selectRaw('sum(coalesce(ingredients.quantity, 1) * meal_plan_entries.servings) as total_quantity')
            ->join('recipes', 'recipes.id', '=', 'ingredients.recipe_id')
            ->join('meal_plan_entries', 'meal_plan_entries.recipe_id', '=', 'recipes.id')
            ->where('meal_plan_entries.user_id', $user->id)
            ->whereBetween('meal_plan_entries.date', $monthRange)
            ->groupBy('ingredients.name', 'ingredients.unit')
            ->orderByDesc('uses')
            ->limit(self::TOP_LIMIT)
            ->get();
    }

    public function shoppingTotal(User $user, string $monthStartDate, string $monthEndDate): float
    {
        return (float) ShoppingListItem::query()
            ->where('user_id', $user->id)
            ->where('range_end', '>=', $monthStartDate)
            ->where('range_start', '<=', $monthEndDate)
            ->sum('price');
    }

    public function customTotal(User $user, string $monthStartDate, string $monthEndDate): float
    {
        return (float) ShoppingListCustomItem::query()
            ->where('user_id', $user->id)
            ->where('range_end', '>=', $monthStartDate)
            ->where('range_start', '<=', $monthEndDate)
            ->sum('price');
    }

    /**
     * @param  array{0: string, 1: string}  $monthRange
     */
    public function topCommunityRecipes(User $user, array $monthRange): Collection
    {
        return MealPlanEntry::query()
            ->whereBetween('date', $monthRange)
            ->whereNotNull('recipe_id')
            ->whereHas('recipe', function ($query) use ($user): void {
                $query->where('is_public', true)
                    ->where('user_id', '!=', $user->id);
            })
            ->select('recipe_id')
            ->selectRaw('count(*) as uses')
            ->groupBy('recipe_id')
            ->orderByDesc('uses')
            ->with([
                'recipe' => function ($query): void {
                    $query->select('id', 'title', 'is_public', 'user_id', 'cover_image_path', 'cover_thumbnail_path')
                        ->selectSub($this->recipeSavesCountSubquery(), 'saves_count')
                        ->with('ingredients:id,recipe_id,name');
                },
                'recipe.originalRecipe:id,cover_image_path,cover_thumbnail_path',
                'recipe.user:id,name',
            ])
            ->limit(self::TOP_LIMIT)
            ->get();
    }

    public function ownedOriginals(User $user, Collection $communityRecipeIds): Collection
    {
        return Recipe::query()
            ->where('user_id', $user->id)
            ->whereNotNull('original_recipe_id')
            ->whereIn('original_recipe_id', $communityRecipeIds)
            ->pluck('id', 'original_recipe_id');
    }

    public function topSavedUsers(): Collection
    {
        return User::query()
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
            ->limit(self::TOP_LIMIT)
            ->get();
    }

    public function topSavedRecipes(): Collection
    {
        return Recipe::query()
            ->whereNull('original_recipe_id')
            ->where('is_public', true)
            ->select('id', 'title', 'cover_image_path', 'cover_thumbnail_path')
            ->selectSub($this->recipeSavesCountSubquery(), 'saves_count')
            ->orderByDesc('saves_count')
            ->limit(self::TOP_LIMIT)
            ->get();
    }

    private function recipeSavesCountSubquery(): Closure
    {
        return function ($query): void {
            $query->from('recipes as copies')
                ->selectRaw('count(distinct copies.user_id)')
                ->whereColumn('copies.original_recipe_id', 'recipes.id')
                ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
        };
    }
}
