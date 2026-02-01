<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Repositories\Dashboard\DashboardRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(private DashboardRepository $repository) {}

    /**
     * @return array{
     *     topRecipes: Collection,
     *     topIngredients: Collection,
     *     displayTotal: string,
     *     currencySymbol: string,
     *     monthLabel: string,
     *     ownedOriginals: Collection,
     *     topCommunityRecipes: Collection,
     *     topSavedUsers: Collection,
     *     topSavedRecipes: Collection
     * }
     */
    public function build(User $user): array
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthRange = $this->monthRange($monthStart);
        $monthStartDate = $monthRange[0];
        $monthEndDate = $monthRange[1];

        $topRecipes = $this->repository->topRecipes($user, $monthRange);
        $topIngredients = $this->repository->topIngredients($user, $monthRange)
            ->map(function ($ingredient) {
                $ingredient->display_quantity = $this->formatQuantity($ingredient->total_quantity);

                return $ingredient;
            });

        $shoppingTotal = $this->repository->shoppingTotal($user, $monthStartDate, $monthEndDate);
        $customTotal = $this->repository->customTotal($user, $monthStartDate, $monthEndDate);

        $monthlyTotal = $shoppingTotal + $customTotal;
        $displayTotal = $this->formatDecimal($monthlyTotal);
        $monthLabel = $monthStart->format('F Y');
        $currencySymbol = config('app.currency_symbol', '$');

        $topCommunityRecipes = $this->repository->topCommunityRecipes($user, $monthRange);
        $communityRecipeIds = $topCommunityRecipes->pluck('recipe_id')->filter()->unique();

        return [
            'topRecipes' => $topRecipes,
            'topIngredients' => $topIngredients,
            'displayTotal' => $displayTotal,
            'currencySymbol' => $currencySymbol,
            'monthLabel' => $monthLabel,
            'ownedOriginals' => $this->repository->ownedOriginals($user, $communityRecipeIds),
            'topCommunityRecipes' => $topCommunityRecipes,
            'topSavedUsers' => $this->repository->topSavedUsers(),
            'topSavedRecipes' => $this->repository->topSavedRecipes(),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function monthRange(CarbonImmutable $monthStart): array
    {
        return [$monthStart->toDateString(), $monthStart->endOfMonth()->toDateString()];
    }

    private function formatQuantity(?float $value): ?string
    {
        if (! $value) {
            return null;
        }

        return $this->formatDecimal($value);
    }

    private function formatDecimal(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
