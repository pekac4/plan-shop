<?php

use App\ImageResizer;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::get('/', function () {
    $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
    $monthEnd = $monthStart->endOfMonth();

    $recipeOfMonth = Recipe::query()
        ->with('user')
        ->where('is_public', true)
        ->whereBetween('created_at', [$monthStart, $monthEnd])
        ->latest('created_at')
        ->first();

    if (! $recipeOfMonth) {
        $author = User::query()
            ->where('email', 'maria_k@example.com')
            ->first();

        if (! $author) {
            $author = User::factory()->create([
                'name' => 'Maria K',
                'email' => 'maria_k@example.com',
            ]);
        }

        $recipeOfMonth = Recipe::factory()->for($author)->create([
            'title' => 'Roasted Veggie Bowl with Lemon Tahini',
            'description' => 'Fresh, fast, and perfect for busy weeks — ready in 25 minutes.',
            'instructions' => 'Roast veggies, whisk tahini sauce, and assemble bowls.',
            'prep_time_minutes' => 10,
            'cook_time_minutes' => 15,
            'servings' => 2,
            'is_public' => true,
            'created_at' => $monthStart->addDays(4),
        ]);

        Ingredient::factory()->for($recipeOfMonth)->create([
            'name' => 'Zucchini',
            'quantity' => 2,
            'unit' => 'pcs',
        ]);
    }

    if ($recipeOfMonth && ! $recipeOfMonth->cover_image_path && function_exists('imagecreatetruecolor')) {
        $disk = Storage::disk('public');
        $directory = 'recipes/'.$recipeOfMonth->id;
        $disk->makeDirectory($directory);

        $coverPath = $directory.'/cover.jpg';
        $thumbPath = $directory.'/cover-thumb.jpg';

        $coverFullPath = $disk->path($coverPath);
        $thumbFullPath = $disk->path($thumbPath);

        $image = imagecreatetruecolor(1200, 800);
        $bg = imagecolorallocate($image, 232, 245, 236);
        $leaf = imagecolorallocate($image, 90, 171, 116);
        $leafDark = imagecolorallocate($image, 51, 120, 77);
        $accent = imagecolorallocate($image, 248, 213, 126);

        imagefilledrectangle($image, 0, 0, 1200, 800, $bg);
        imagefilledellipse($image, 260, 260, 340, 380, $leaf);
        imagefilledellipse($image, 460, 320, 280, 330, $leafDark);
        imagefilledellipse($image, 940, 240, 320, 360, $leaf);
        imagefilledellipse($image, 860, 360, 260, 280, $leafDark);
        imagefilledellipse($image, 640, 560, 360, 220, $accent);

        $hash = Str::of($recipeOfMonth->title)->upper()->substr(0, 14);
        imagestring($image, 5, 36, 36, $hash->toString(), $leafDark);

        imagejpeg($image, $coverFullPath, 86);
        imagedestroy($image);

        ImageResizer::resizeToFit($coverFullPath, $thumbFullPath, 720, 480);

        $recipeOfMonth->forceFill([
            'cover_image_path' => $coverPath,
            'cover_thumbnail_path' => $thumbPath,
        ])->save();
    }

    return view('welcome', [
        'recipeOfMonth' => $recipeOfMonth,
        'monthLabel' => $monthStart->format('F Y'),
    ]);
})->name('home');

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
