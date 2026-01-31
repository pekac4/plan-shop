<?php

namespace App\Services\Home;

use App\HighlightRecipeSelector;
use App\Models\Recipe;
use App\Services\Recipes\RecipeCoverService;
use Carbon\CarbonImmutable;

class HomeService
{
    public function __construct(
        private HighlightRecipeSelector $highlightRecipeSelector,
        private RecipeCoverService $recipeCoverService
    ) {}

    /**
     * @return array{recipeOfMonth: Recipe, monthLabel: string}
     */
    public function build(): array
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $recipeOfMonth = $this->highlightRecipeSelector->chefOfMonth($monthStart, $monthEnd);
        $this->recipeCoverService->ensureCover($recipeOfMonth);

        return [
            'recipeOfMonth' => $recipeOfMonth,
            'monthLabel' => $monthStart->format('F Y'),
        ];
    }
}
