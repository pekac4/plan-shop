<?php

namespace App\Http\Controllers;

use App\Services\Highlights\KingOfRecipeService;
use Illuminate\View\View;

class KingOfRecipeController extends Controller
{
    public function __construct(private KingOfRecipeService $kingOfRecipeService) {}

    public function __invoke(): View
    {
        return view('highlights.king-of-the-recipe', $this->kingOfRecipeService->build());
    }
}
