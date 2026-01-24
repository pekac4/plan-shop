<?php

namespace Database\Seeders;

use App\ImageResizer;
use App\Models\Ingredient;
use App\Models\MealPlanEntry;
use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $users = User::query()
            ->select('id')
            ->get();

        if ($users->count() === 1) {
            $users->push(User::factory()->create());
        }

        $primaryUserId = $users->first()?->id;

        $users->each(function (User $user) use ($monthStart, $monthEnd, $primaryUserId): void {
            $hasEntries = MealPlanEntry::query()
                ->where('user_id', $user->id)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->exists();

            if ($hasEntries) {
                if ($primaryUserId && $user->id !== $primaryUserId) {
                    $hasPublicUsage = MealPlanEntry::query()
                        ->where('user_id', $user->id)
                        ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->whereNotNull('recipe_id')
                        ->whereHas('recipe', fn ($query) => $query->where('is_public', true))
                        ->exists();

                    if ($hasPublicUsage) {
                        return;
                    }
                } else {
                    return;
                }
            }

            $recipes = Recipe::factory()
                ->for($user)
                ->count(5)
                ->create([
                    'is_public' => $primaryUserId ? $user->id !== $primaryUserId : false,
                ]);

            foreach ($recipes as $recipe) {
                Ingredient::factory()
                    ->for($recipe)
                    ->count(2)
                    ->create([
                        'price' => fake()->randomFloat(2, 0.5, 5),
                    ]);

                if ($recipe->is_public) {
                    $this->attachCoverImage($recipe);
                }
            }

            $meals = MealPlanEntry::MEALS;
            $recipeIndex = 0;
            $days = $monthStart->diffInDays($monthEnd) + 1;

            for ($offset = 0; $offset < $days; $offset++) {
                $date = $monthStart->addDays($offset)->toDateString();

                foreach ($meals as $meal) {
                    $recipe = $recipes[$recipeIndex % $recipes->count()];
                    $recipeIndex++;

                    MealPlanEntry::query()->updateOrCreate([
                        'user_id' => $user->id,
                        'date' => $date,
                        'meal' => $meal,
                    ], [
                        'recipe_id' => $recipe->id,
                        'custom_title' => null,
                        'servings' => fake()->numberBetween(1, 3),
                    ]);
                }
            }
        });

        $otherUser = $users->firstWhere(fn (User $user) => $user->id !== $primaryUserId);

        if ($otherUser) {
            $publicRecipe = Recipe::query()
                ->where('user_id', $otherUser->id)
                ->where('is_public', true)
                ->first();

            if ($publicRecipe) {
                $this->attachCoverImage($publicRecipe);

                Recipe::factory()
                    ->for($otherUser)
                    ->count(3)
                    ->create([
                        'original_recipe_id' => $publicRecipe->id,
                        'title' => 'Copy of '.$publicRecipe->title,
                        'is_public' => false,
                        'created_at' => $monthStart->addDays(2),
                    ]);
            }
        }
    }

    protected function attachCoverImage(Recipe $recipe): void
    {
        if ($recipe->cover_image_path || $recipe->cover_thumbnail_path) {
            return;
        }

        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $disk = Storage::disk('public');
        $directory = 'recipes/'.$recipe->id;
        $disk->makeDirectory($directory);

        $coverPath = $directory.'/cover.jpg';
        $thumbPath = $directory.'/cover-thumb.jpg';

        $coverFullPath = $disk->path($coverPath);
        $thumbFullPath = $disk->path($thumbPath);

        $this->generateVeggiePlaceholder($coverFullPath, $recipe->title);
        ImageResizer::resizeToFit($coverFullPath, $thumbFullPath, 420, 320);

        $recipe->forceFill([
            'cover_image_path' => $coverPath,
            'cover_thumbnail_path' => $thumbPath,
        ])->save();
    }

    protected function generateVeggiePlaceholder(string $path, string $title): void
    {
        $width = 1200;
        $height = 800;

        $image = imagecreatetruecolor($width, $height);

        $bg = imagecolorallocate($image, 232, 245, 236);
        $leaf = imagecolorallocate($image, 90, 171, 116);
        $leafDark = imagecolorallocate($image, 51, 120, 77);
        $accent = imagecolorallocate($image, 248, 213, 126);

        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        imagefilledellipse($image, 250, 260, 320, 380, $leaf);
        imagefilledellipse($image, 420, 300, 280, 340, $leafDark);
        imagefilledellipse($image, 980, 220, 300, 360, $leaf);
        imagefilledellipse($image, 860, 330, 260, 300, $leafDark);
        imagefilledellipse($image, 620, 560, 360, 220, $accent);

        $hash = Str::of($title)->upper()->substr(0, 14);
        imagestring($image, 5, 36, 36, $hash->toString(), $leafDark);

        imagejpeg($image, $path, 86);
        imagedestroy($image);
    }
}
