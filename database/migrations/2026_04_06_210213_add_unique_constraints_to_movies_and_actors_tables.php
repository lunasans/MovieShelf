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
        Schema::table('movies', function (Blueprint $table) {
            // Uniquely identify movies by TMDb ID and type (movie/tv)
            // Existing duplicates should have been cleaned up by the resolution script
            $table->unique(['tmdb_id', 'tmdb_type'], 'idx_movie_tmdb_unique');
        });

        Schema::table('actors', function (Blueprint $table) {
            $table->unique('tmdb_id', 'idx_actor_tmdb_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropUnique('idx_movie_tmdb_unique');
        });

        Schema::table('actors', function (Blueprint $table) {
            $table->dropUnique('idx_actor_tmdb_unique');
        });
    }
};
