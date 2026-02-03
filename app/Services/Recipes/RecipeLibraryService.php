<?php

namespace App\Services\Recipes;

use App\Models\Recipe;
use App\Models\User;

class RecipeLibraryService
{
    public function addToLibrary(User $user, Recipe $recipe): void
    {
        if ($recipe->user_id === $user->id) {
            return;
        }

        $originalId = $recipe->original_recipe_id ?? $recipe->id;

        $existing = Recipe::query()
            ->where('user_id', $user->id)
            ->where('original_recipe_id', $originalId)
            ->first();

        if ($existing) {
            return;
        }

        $copy = $recipe->replicate(['is_public', 'cover_image_path', 'cover_thumbnail_path']);
        $copy->original_recipe_id = $originalId;
        $copy->user_id = $user->id;
        $copy->title = $recipe->title;
        $copy->is_public = true;
        $copy->save();

        $copy->ingredients()->createMany(
            $recipe->ingredients()
                ->get()
                ->map(fn ($ingredient) => $ingredient->only(['name', 'quantity', 'unit', 'note', 'price']))
                ->all(),
        );
    }
}
