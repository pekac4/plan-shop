<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $recipe_id
 * @property \Illuminate\Support\Carbon $date
 * @property int|null $servings
 * @property Recipe|null $recipe
 * @property User $user
 */
class MealPlanEntry extends Model
{
    /** @use HasFactory<\Database\Factories\MealPlanEntryFactory> */
    use HasFactory;

    public const MEALS = [
        'breakfast',
        'lunch',
        'dinner',
        'snack',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'meal',
        'recipe_id',
        'custom_title',
        'servings',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'servings' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
