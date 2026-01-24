<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::livewire('recipes', 'pages::recipes.index')->name('recipes.index');
    Route::livewire('recipes/create', 'pages::recipes.create')->name('recipes.create');
    Route::livewire('recipes/{recipe}/edit', 'pages::recipes.edit')->name('recipes.edit');

    Route::livewire('meal-plan', 'pages::meal-plan.index')->name('meal-plan.index');
    Route::livewire('shopping-list', 'pages::shopping-list.index')->name('shopping-list.index');
});

require __DIR__.'/settings.php';
