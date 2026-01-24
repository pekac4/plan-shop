<?php

namespace App\Policies;

use App\Models\MealPlanEntry;
use App\Models\User;

class MealPlanEntryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MealPlanEntry $mealPlanEntry): bool
    {
        return $mealPlanEntry->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MealPlanEntry $mealPlanEntry): bool
    {
        return $mealPlanEntry->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MealPlanEntry $mealPlanEntry): bool
    {
        return $mealPlanEntry->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MealPlanEntry $mealPlanEntry): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MealPlanEntry $mealPlanEntry): bool
    {
        return false;
    }
}
