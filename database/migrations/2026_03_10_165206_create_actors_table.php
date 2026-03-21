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
        if (! Schema::hasTable('actors')) {
            Schema::create('actors', function (Blueprint $table) {
                $table->id();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->integer('birth_year')->nullable();
                $table->text('bio')->nullable();
                $table->timestamps();

                $table->index(['last_name', 'first_name'], 'idx_name');
                $table->index('birth_year', 'idx_birth_year');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actors');
    }
};
