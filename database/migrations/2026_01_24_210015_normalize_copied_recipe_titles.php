<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                update recipes
                set title = (
                    select originals.title
                    from recipes as originals
                    where originals.id = recipes.original_recipe_id
                )
                where original_recipe_id is not null
            ');

            return;
        }

        DB::table('recipes as copies')
            ->join('recipes as originals', 'copies.original_recipe_id', '=', 'originals.id')
            ->whereNotNull('copies.original_recipe_id')
            ->update([
                'copies.title' => DB::raw('originals.title'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive: original titles cannot be restored once normalized.
    }
};
