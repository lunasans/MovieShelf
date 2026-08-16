<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('list_movies', function (Blueprint $table) {
            $table->foreignId('list_id')->constrained('lists')->onDelete('cascade');
            $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
            $table->timestamp('added_at')->useCurrent();
            $table->primary(['list_id', 'movie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_movies');
        Schema::dropIfExists('lists');
    }
};
