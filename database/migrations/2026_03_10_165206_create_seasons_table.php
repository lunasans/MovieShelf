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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->integer('season_number');
            $table->string('title', 255)->nullable();
            $table->text('overview')->nullable();
            $table->timestamps();

            $table->index('movie_id', 'idx_movie');
            $table->index(['movie_id', 'season_number'], 'idx_movie_season');

            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
