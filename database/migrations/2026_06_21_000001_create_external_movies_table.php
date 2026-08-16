<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('external_movies')) {
            Schema::create('external_movies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('title', 255);
                $table->integer('year')->nullable();
                $table->string('genre', 255)->nullable();
                $table->string('director', 255)->nullable();
                $table->integer('runtime')->nullable();
                $table->decimal('rating', 4, 1)->nullable();
                $table->integer('rating_age')->nullable();
                $table->text('overview')->nullable();
                $table->string('collection_type', 100)->nullable();
                $table->string('cover_id', 200)->nullable();
                $table->string('backdrop_id', 200)->nullable();
                $table->string('trailer_url', 500)->nullable();
                $table->unsignedBigInteger('tmdb_id')->nullable();
                $table->string('tmdb_type', 20)->nullable();
                $table->timestamps();

                $table->index('user_id', 'idx_external_user');
                $table->index('tmdb_id', 'idx_external_tmdb');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('external_movies');
    }
};
