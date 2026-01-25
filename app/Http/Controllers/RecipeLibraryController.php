<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RecipeLibraryController extends Controller
{
    use AuthorizesRequests;

    public function store(Recipe $recipe): RedirectResponse
    {
        $this->authorize('duplicate', $recipe);

        if ($recipe->user_id === Auth::id()) {
            return redirect()->back();
        }

        $originalId = $recipe->original_recipe_id ?? $recipe->id;

        $existing = Recipe::query()
            ->where('user_id', Auth::id())
            ->where('original_recipe_id', $originalId)
            ->first();

        if ($existing) {
            return redirect()->back();
        }

        $copy = $recipe->replicate(['is_public']);
        $copy->original_recipe_id = $originalId;
        $copy->user_id = Auth::id();
        $copy->title = $recipe->title;
        $copy->is_public = true;
        $copy->save();

        $copy->ingredients()->createMany(
            $recipe->ingredients()
                ->get()
                ->map(fn ($ingredient) => $ingredient->only(['name', 'quantity', 'unit', 'note', 'price']))
                ->all(),
        );

        return redirect()->back();
    }
}
