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
        Schema::create('external_installations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('php_version')->nullable();
            $table->string('laravel_version')->nullable();
            $table->string('app_version')->nullable();
            $table->integer('movie_count')->default(0);
            $table->integer('actor_count')->default(0);
            $table->integer('user_count')->default(0);
            $table->string('os')->nullable();
            $table->string('db_driver')->nullable();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->json('extra_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_installations');
    }
};
