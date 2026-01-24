<?php

use App\Models\Recipe;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('navigates primary links from the header', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit(route('recipes.index'))->on()->macbook16()
        ->assertSee('Recipes');

    $page->click('@nav-meal-plan')
        ->waitForText('Meal Plan');

    $page->click('@nav-shopping-list')
        ->waitForText('Shopping List');

    $page->click('@nav-profile')
        ->waitForText('Profile');
});

it('creates a recipe, plans it, and generates a shopping list', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit(route('recipes.create'))->on()->macbook16()
        ->type('title', 'Veggie Bowl')
        ->type('instructions', 'Mix fresh veggies and serve.')
        ->type('[name="ingredients.0.name"]', 'Tomato')
        ->type('[name="ingredients.0.quantity"]', '2')
        ->type('[name="ingredients.0.unit"]', 'pcs')
        ->press('Save Recipe')
        ->waitForText('Veggie Bowl');

    $recipe = Recipe::query()->first();

    $slotKey = CarbonImmutable::now()->startOfWeek(CarbonImmutable::MONDAY)->toDateString().'|dinner';

    $page->click('@nav-meal-plan')
        ->waitForText('Meal Plan')
        ->click('@meal-add-'.$slotKey)
        ->select('formRecipeId', $recipe->id)
        ->type('formServings', '2')
        ->press('@meal-save')
        ->waitForText('Veggie Bowl');

    $page->click('@nav-shopping-list')
        ->waitForText('Shopping List')
        ->press('@shopping-generate')
        ->waitForText('Tomato');

    $itemId = ShoppingListItem::query()
        ->where('user_id', $user->id)
        ->value('id');

    expect($itemId)->not->toBeNull();

    $page->check('@shopping-item-'.$itemId)
        ->assertChecked('@shopping-item-'.$itemId);
});
