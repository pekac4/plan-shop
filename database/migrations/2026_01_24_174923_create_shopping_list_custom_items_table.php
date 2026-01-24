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
        Schema::create('shopping_list_custom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('custom_shopping_item_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->date('range_start');
            $table->date('range_end');
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'range_start', 'range_end']);
            $table->unique(['user_id', 'custom_shopping_item_id', 'range_start', 'range_end'], 'shopping_custom_range_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_list_custom_items');
    }
};
