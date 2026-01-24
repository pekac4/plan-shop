<?php

namespace App\Providers;

use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Policies\MealPlanEntryPolicy;
use App\Policies\RecipePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(Recipe::class, RecipePolicy::class);
        Gate::policy(MealPlanEntry::class, MealPlanEntryPolicy::class);
    }
}
