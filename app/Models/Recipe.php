<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $original_recipe_id
 * @property string $title
 * @property string|null $description
 * @property int|null $servings
 * @property int|null $prep_time_minutes
 * @property int|null $cook_time_minutes
 * @property string|null $instructions
 * @property string|null $source_url
 * @property string|null $cover_image_path
 * @property string|null $cover_thumbnail_path
 * @property bool $is_public
 * @property \Illuminate\Database\Eloquent\Collection<int, Ingredient> $ingredients
 * @property User $user
 * @property Recipe|null $originalRecipe
 * @property \Illuminate\Database\Eloquent\Collection<int, Recipe> $copies
 */
class Recipe extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'original_recipe_id',
        'title',
        'description',
        'servings',
        'prep_time_minutes',
        'cook_time_minutes',
        'instructions',
        'source_url',
        'cover_image_path',
        'cover_thumbnail_path',
        'is_public',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'servings' => 'integer',
            'prep_time_minutes' => 'integer',
            'cook_time_minutes' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    public function originalRecipe(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_recipe_id');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(self::class, 'original_recipe_id');
    }

    public function getApproximatePriceAttribute(): float
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Ingredient> $ingredients */
        $ingredients = $this->relationLoaded('ingredients')
            ? $this->ingredients
            : $this->ingredients()->get();

        $total = $ingredients->reduce(function (float $carry, Ingredient $ingredient): float {
            $price = (float) $ingredient->price;
            $quantity = $ingredient->quantity;

            if ($quantity === null) {
                return $carry + $price;
            }

            return $carry + ($price * (float) $quantity);
        }, 0.0);

        return round($total, 2);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (! $this->cover_image_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->cover_image_path);
    }

    public function getCoverThumbnailUrlAttribute(): ?string
    {
        if (! $this->cover_thumbnail_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->cover_thumbnail_path);
    }
}
