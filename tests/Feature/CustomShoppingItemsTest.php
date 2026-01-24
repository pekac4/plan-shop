<?php

use App\Actions\ShoppingList\BuildShoppingList;
use App\Models\CustomShoppingItem;
use App\Models\ShoppingListCustomItem;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('includes custom items in the shopping list for the selected range', function () {
    $user = User::factory()->create();
    $customItem = CustomShoppingItem::factory()->for($user)->create([
        'name' => 'Coffee',
        'price' => 4.5,
    ]);

    ShoppingListCustomItem::factory()->for($customItem, 'customItem')->for($user)->create([
        'range_start' => '2026-01-20',
        'range_end' => '2026-01-26',
        'quantity' => 2,
        'price' => 9.0,
    ]);

    $items = app(BuildShoppingList::class)->handle(
        $user,
        CarbonImmutable::parse('2026-01-20'),
        CarbonImmutable::parse('2026-01-26'),
    );

    $coffee = collect($items)->firstWhere(fn (array $item) => $item['name'] === 'Coffee');

    expect($coffee)->not()->toBeNull()
        ->and($coffee['is_custom'])->toBeTrue()
        ->and($coffee['price'])->toBe('9.00')
        ->and($coffee['display_quantity'])->toBe('2');
});

it('creates or reuses custom items from the shopping list page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::shopping-list.index')
        ->set('rangeStart', '2026-01-20')
        ->set('rangeEnd', '2026-01-26')
        ->set('customName', 'Cigarettes')
        ->set('customPrice', 6.5)
        ->set('customQuantity', 2)
        ->call('addCustomItem');

    assertDatabaseHas('custom_shopping_items', [
        'user_id' => $user->id,
        'name' => 'Cigarettes',
        'price' => 6.5,
    ]);

    assertDatabaseHas('shopping_list_custom_items', [
        'user_id' => $user->id,
        'range_start' => '2026-01-20',
        'range_end' => '2026-01-26',
        'price' => 13.0,
    ]);

    Livewire::test('pages::shopping-list.index')
        ->set('rangeStart', '2026-01-20')
        ->set('rangeEnd', '2026-01-26')
        ->set('customName', 'Cigarettes')
        ->set('customQuantity', 1)
        ->call('addCustomItem');

    assertDatabaseHas('custom_shopping_items', [
        'user_id' => $user->id,
        'name' => 'Cigarettes',
        'price' => 6.5,
    ]);
});
