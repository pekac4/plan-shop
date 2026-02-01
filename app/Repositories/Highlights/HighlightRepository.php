<?php

namespace App\Repositories\Highlights;

use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Collection;

class HighlightRepository
{
    private const LEADER_RECIPES_LIMIT = 10;

    public function kingLeader(): ?User
    {
        return User::query()
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
    }

    public function kingLeaderRecipes(User $leader): Collection
    {
        return Recipe::query()
            ->whereNull('original_recipe_id')
            ->where('is_public', true)
            ->where('user_id', $leader->id)
            ->select('recipes.id', 'recipes.title', 'recipes.cover_image_path', 'recipes.cover_thumbnail_path')
            ->selectSub($this->recipeSavesCountSubquery(), 'stars')
            ->orderByDesc('stars')
            ->limit(self::LEADER_RECIPES_LIMIT)
            ->get();
    }

    public function upcomingLeader(CarbonImmutable $monthStart, CarbonImmutable $monthEnd): ?User
    {
        return User::query()
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
    }

    public function upcomingLeaderRecipes(User $leader, CarbonImmutable $monthStart, CarbonImmutable $monthEnd): Collection
    {
        return Recipe::query()
            ->whereNull('original_recipe_id')
            ->where('is_public', true)
            ->where('user_id', $leader->id)
            ->select('recipes.id', 'recipes.title', 'recipes.cover_image_path', 'recipes.cover_thumbnail_path')
            ->selectSub($this->recipeSavesCountForPeriodSubquery($monthStart, $monthEnd), 'last_month_copies')
            ->whereHas('copies', function ($query) use ($monthStart, $monthEnd): void {
                $query->whereBetween('created_at', [$monthStart, $monthEnd]);
            })
            ->orderByDesc('last_month_copies')
            ->limit(self::LEADER_RECIPES_LIMIT)
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

    private function recipeSavesCountForPeriodSubquery(CarbonImmutable $monthStart, CarbonImmutable $monthEnd): Closure
    {
        return function ($query) use ($monthStart, $monthEnd): void {
            $query->from('recipes as copies')
                ->selectRaw('count(distinct copies.user_id)')
                ->whereColumn('copies.original_recipe_id', 'recipes.id')
                ->whereColumn('copies.user_id', '!=', 'recipes.user_id')
                ->whereBetween('copies.created_at', [$monthStart, $monthEnd]);
        };
    }
}
