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
        DB::table('recipes')
            ->whereNotNull('original_recipe_id')
            ->update(['is_public' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('recipes')
            ->whereNotNull('original_recipe_id')
            ->update(['is_public' => false]);
    }
};
