<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $custom_shopping_item_id
 * @property string $range_start
 * @property string $range_end
 * @property string|null $quantity
 * @property string|null $price
 * @property \Illuminate\Support\Carbon|null $checked_at
 * @property CustomShoppingItem|null $customItem
 * @property User $user
 */
class ShoppingListCustomItem extends Model
{
    /** @use HasFactory<\Database\Factories\ShoppingListCustomItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'custom_shopping_item_id',
        'range_start',
        'range_end',
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
            'range_start' => 'date:Y-m-d',
            'range_end' => 'date:Y-m-d',
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'checked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customItem(): BelongsTo
    {
        return $this->belongsTo(CustomShoppingItem::class, 'custom_shopping_item_id');
    }
}
