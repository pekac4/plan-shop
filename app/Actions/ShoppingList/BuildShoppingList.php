<?php

namespace App\Actions\ShoppingList;

use App\Models\MealPlanEntry;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BuildShoppingList
{
    /**
     * @return list<array{id: int, name: string, unit: string|null, quantity: string|null, display_quantity: string|null, price: string|null, checked_at: string|null, source_recipes_count: int}>
     */
    public function handle(User $user, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd): array
    {
        $entries = MealPlanEntry::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->whereNotNull('recipe_id')
            ->with(['recipe.ingredients'])
            ->get();

        $aggregated = [];
        $nonAggregated = [];

        foreach ($entries as $entry) {
            if (! $entry->recipe) {
                continue;
            }

            foreach ($entry->recipe->ingredients as $ingredient) {
                $name = $this->normalizeName($ingredient->name);
                $displayName = trim($ingredient->name);
                $unit = $ingredient->unit ? trim($ingredient->unit) : null;
                $quantity = $ingredient->quantity;

                $price = $ingredient->price ?? 0;
                $servings = max(1, (int) $entry->servings);

                if ($quantity === null) {
                    $nonAggregated[] = [
                        'name' => $displayName,
                        'unit' => $unit,
                        'quantity' => null,
                        'display_quantity' => null,
                        'price' => $this->formatStoredPrice((float) $price * $servings),
                        'recipe_ids' => [$entry->recipe->id],
                    ];

                    continue;
                }

                $multiplied = round((float) $quantity * $servings, 2);
                $priceTotal = round((float) $price * $multiplied, 2);
                $key = $name.'|'.($unit ?? '');

                if (! array_key_exists($key, $aggregated)) {
                    $aggregated[$key] = [
                        'name' => $displayName,
                        'unit' => $unit,
                        'quantity' => 0.0,
                        'price' => 0.0,
                        'recipe_ids' => [],
                    ];
                }

                $aggregated[$key]['quantity'] += $multiplied;
                $aggregated[$key]['price'] += $priceTotal;
                $aggregated[$key]['recipe_ids'][] = $entry->recipe->id;
            }
        }

        $items = collect(array_values($aggregated))
            ->map(function (array $item): array {
                $quantity = round((float) $item['quantity'], 2);
                $price = round((float) $item['price'], 2);
                $storedQuantity = $this->formatStoredQuantity($quantity);
                $storedPrice = $this->formatStoredPrice($price);

                return [
                    'name' => $item['name'],
                    'unit' => $item['unit'],
                    'quantity' => $storedQuantity,
                    'display_quantity' => $this->formatDisplayQuantity($storedQuantity),
                    'price' => $storedPrice,
                    'recipe_ids' => array_values(array_unique($item['recipe_ids'])),
                ];
            })
            ->merge($nonAggregated)
            ->values();

        return $this->persistList($user, $rangeStart, $rangeEnd, $items)->all();
    }

    protected function persistList(User $user, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd, Collection $items): Collection
    {
        $existing = ShoppingListItem::query()
            ->where('user_id', $user->id)
            ->forRange($rangeStart->toDateString(), $rangeEnd->toDateString())
            ->get()
            ->keyBy(fn (ShoppingListItem $item): string => $this->persistKey($item->name, $item->unit, $item->quantity));

        ShoppingListItem::query()
            ->where('user_id', $user->id)
            ->forRange($rangeStart->toDateString(), $rangeEnd->toDateString())
            ->delete();

        return $items->map(function (array $item) use ($existing, $user, $rangeStart, $rangeEnd): array {
            $quantity = $item['quantity'];
            $persistKey = $this->persistKey($item['name'], $item['unit'], $quantity);
            $checkedAt = $existing->get($persistKey)?->checked_at;

            $stored = ShoppingListItem::query()->create([
                'user_id' => $user->id,
                'range_start' => $rangeStart->toDateString(),
                'range_end' => $rangeEnd->toDateString(),
                'name' => $item['name'],
                'unit' => $item['unit'],
                'quantity' => $quantity,
                'price' => $item['price'],
                'checked_at' => $checkedAt,
            ]);

            return [
                'id' => $stored->id,
                'name' => $stored->name,
                'unit' => $stored->unit,
                'quantity' => $stored->quantity,
                'display_quantity' => $this->formatDisplayQuantity($stored->quantity),
                'price' => $this->formatDisplayPrice($stored->price),
                'checked_at' => $stored->checked_at?->toDateTimeString(),
                'source_recipes_count' => count(array_unique($item['recipe_ids'] ?? [])),
            ];
        });
    }

    protected function persistKey(string $name, ?string $unit, ?string $quantity): string
    {
        return $this->normalizeName($name).'|'.($unit ? Str::lower(trim($unit)) : '').'|'.($quantity ?? 'null');
    }

    protected function normalizeName(string $name): string
    {
        return Str::lower(Str::squish($name));
    }

    protected function formatStoredQuantity(float $quantity): string
    {
        return number_format($quantity, 2, '.', '');
    }

    protected function formatDisplayQuantity(?string $quantity): ?string
    {
        if ($quantity === null) {
            return null;
        }

        return rtrim(rtrim($quantity, '0'), '.');
    }

    protected function formatStoredPrice(float $price): string
    {
        return number_format($price, 2, '.', '');
    }

    protected function formatDisplayPrice(?string $price): ?string
    {
        if ($price === null) {
            return null;
        }

        return rtrim(rtrim($price, '0'), '.');
    }
}
