<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->foreignId('original_recipe_id')
                ->nullable()
                ->constrained('recipes')
                ->nullOnDelete();

            $table->index(['original_recipe_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropIndex(['original_recipe_id', 'created_at']);
            $table->dropConstrainedForeignId('original_recipe_id');
        });
    }
};
