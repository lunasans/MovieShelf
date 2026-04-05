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
        if (! Schema::hasTable('counter')) {
            Schema::create('counter', function (Blueprint $table) {
                $table->id();
                $table->string('page', 255);
                $table->integer('visits')->default(0);
                $table->date('last_visit')->nullable();
                $table->timestamps();

                $table->index('page', 'idx_page');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counter');
    }
};
