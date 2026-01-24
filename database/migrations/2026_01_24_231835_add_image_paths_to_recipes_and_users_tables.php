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
            $table->string('cover_image_path')->nullable()->after('source_url');
            $table->string('cover_thumbnail_path')->nullable()->after('cover_image_path');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['cover_image_path', 'cover_thumbnail_path']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });
    }
};
