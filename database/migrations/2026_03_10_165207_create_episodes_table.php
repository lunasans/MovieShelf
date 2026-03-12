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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('season_id');
            $table->integer('episode_number');
            $table->string('title', 255)->nullable();
            $table->text('overview')->nullable();
            $table->timestamps();

            $table->index('season_id', 'idx_season');
            $table->index(['season_id', 'episode_number'], 'idx_season_episode');

            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
