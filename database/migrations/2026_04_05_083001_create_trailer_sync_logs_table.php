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
        Schema::create('trailer_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('trailer_sync_runs')->onDelete('cascade');
            $table->foreignId('movie_id')->nullable()->constrained('movies')->onDelete('set null');
            $table->string('movie_title')->nullable();
            $table->string('status'); // found, not_found, error
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trailer_sync_logs');
    }
};
