<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $range_start
 * @property string $range_end
 * @property string $name
 * @property string|null $unit
 * @property string|null $quantity
 * @property string|null $price
 * @property \Illuminate\Support\Carbon|null $checked_at
 * @property User $user
 */
class ShoppingListItem extends Model
{
    /** @use HasFactory<\Database\Factories\ShoppingListItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'range_start',
        'range_end',
        'name',
        'unit',
        'quantity',
        'price',
        'checked_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'range_start' => 'date',
            'range_end' => 'date',
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'checked_at' => 'datetime',
        ];
    }

    public function scopeForRange(Builder $query, string $rangeStart, string $rangeEnd): Builder
    {
        return $query
            ->where('range_start', $rangeStart)
            ->where('range_end', $rangeEnd);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
