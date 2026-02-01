<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\Recipes\RecipeLibraryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RecipeLibraryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private RecipeLibraryService $recipeLibraryService) {}

    public function store(Recipe $recipe): RedirectResponse
    {
        $this->authorize('duplicate', $recipe);

        $user = Auth::user();

        if (! $user) {
            return redirect()->back();
        }

        $this->recipeLibraryService->addToLibrary($user, $recipe);

        return redirect()->back();
    }
}
