<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class HighlightDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $copyDate = $monthStart->addDays(7);

        $leader = User::query()->where('email', 'kristinahicil5@gmail.com')->first();

        if (! $leader) {
            $leader = User::factory()->create([
                'name' => 'Kristina',
                'email' => 'kristinahicil5@gmail.com',
            ]);
        }

        $copyUsers = User::query()
            ->whereIn('email', [
                'copy-one@example.com',
                'copy-two@example.com',
                'copy-three@example.com',
            ])
            ->get()
            ->keyBy('email');

        foreach (['copy-one@example.com', 'copy-two@example.com', 'copy-three@example.com'] as $email) {
            if (! $copyUsers->has($email)) {
                $copyUsers[$email] = User::factory()->create([
                    'name' => ucfirst(str_replace(['copy-', '-'], ['', ' '], explode('@', $email)[0])),
                    'email' => $email,
                ]);
            }
        }

        $recipes = Recipe::query()
            ->whereNull('original_recipe_id')
            ->where('user_id', $leader->id)
            ->where('is_public', true)
            ->limit(2)
            ->get();

        while ($recipes->count() < 2) {
            $recipe = Recipe::factory()->for($leader)->create([
                'title' => $recipes->count() === 0 ? "Kristina's Garden Salad" : 'Citrus Lentil Bowl',
                'description' => 'Fresh, bright, and perfect for the week ahead.',
                'instructions' => 'Prep ingredients, toss together, serve chilled.',
                'is_public' => true,
                'created_at' => $monthStart->addDays(2 + $recipes->count()),
            ]);

            Ingredient::factory()->for($recipe)->create([
                'name' => 'Cucumber',
                'quantity' => 1,
                'unit' => 'pcs',
            ]);

            $recipes = $recipes->push($recipe);
        }

        foreach ($recipes as $index => $original) {
            $copiesNeeded = $index === 0 ? 2 : 1;

            $copyUsers->values()->take($copiesNeeded)->each(function (User $user) use ($original, $copyDate): void {
                $existing = Recipe::query()
                    ->where('user_id', $user->id)
                    ->where('original_recipe_id', $original->id)
                    ->first();

                if ($existing) {
                    return;
                }

                $copy = $original->replicate(['is_public']);
                $copy->user_id = $user->id;
                $copy->original_recipe_id = $original->id;
                $copy->is_public = true;
                $copy->created_at = $copyDate;
                $copy->save();
            });
        }
    }
}
