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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->integer('year')->nullable();
            $table->string('genre', 255)->nullable();
            $table->string('cover_id', 200)->nullable();
            $table->string('collection_type', 100)->nullable();
            $table->integer('runtime')->nullable();
            $table->integer('rating_age')->nullable();
            $table->text('overview')->nullable();
            $table->string('trailer_url', 500)->nullable();
            $table->unsignedBigInteger('boxset_parent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->boolean('deleted')->default(false);

            $table->index('title', 'idx_title');
            $table->index('year', 'idx_year');
            $table->index('genre', 'idx_genre');
            $table->index('collection_type', 'idx_collection_type');
            $table->index('user_id', 'idx_movie_user');
            $table->index('is_deleted', 'idx_deleted');
            $table->index('boxset_parent', 'idx_boxset_parent');
            $table->index('view_count', 'idx_view_count');
            // $table->fullText(['title', 'overview'], 'idx_search');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('boxset_parent')->references('id')->on('movies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
