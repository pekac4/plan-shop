<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::livewire('recipes', 'pages::recipes.index')->name('recipes.index');
    Route::livewire('recipes/create', 'pages::recipes.create')->name('recipes.create');
    Route::livewire('recipes/{recipe}/edit', 'pages::recipes.edit')->name('recipes.edit');
    Route::post('recipes/{recipe}/add-to-library', [\App\Http\Controllers\RecipeLibraryController::class, 'store'])
        ->name('recipes.add-to-library');

    Route::livewire('meal-plan', 'pages::meal-plan.index')->name('meal-plan.index');
    Route::livewire('shopping-list', 'pages::shopping-list.index')->name('shopping-list.index');
});

require __DIR__.'/settings.php';
