<?php

namespace App\Services\Highlights;

use App\HighlightRecipeSelector;
use App\Models\Recipe;
use Carbon\CarbonImmutable;

class ChefOfMonthService
{
    public function __construct(private HighlightRecipeSelector $highlightRecipeSelector) {}

    /**
     * @return array{recipe: Recipe, monthLabel: string}
     */
    public function build(): array
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        return [
            'recipe' => $this->highlightRecipeSelector->chefOfMonth($monthStart, $monthEnd),
            'monthLabel' => $monthStart->format('F Y'),
        ];
    }
}
