<?php

namespace App\Http\Controllers;

use App\HighlightRecipeSelector;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class ChefOfMonthController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $recipe = (new HighlightRecipeSelector)->chefOfMonth($monthStart, $monthEnd);

        return view('highlights.chef-of-the-month', [
            'recipe' => $recipe,
            'monthLabel' => $monthStart->format('F Y'),
        ]);
    }
}
