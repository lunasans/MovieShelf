<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desktop_releases', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('version')->unique();
            $blueprint->text('changelog')->nullable();
            $blueprint->string('download_url')->nullable();
            $blueprint->string('file_path')->nullable(); // Falls lokal gespeichert
            $blueprint->boolean('is_public')->default(false);
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desktop_releases');
    }
};
