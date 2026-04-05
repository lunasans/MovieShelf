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
        Schema::table('actors', function (Blueprint $table) {
            $table->string('imdb_id', 20)->nullable()->after('tmdb_id');
            $table->string('homepage')->nullable()->after('place_of_birth');
            $table->unsignedInteger('view_count')->default(0)->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actors', function (Blueprint $table) {
            $table->dropColumn(['imdb_id', 'homepage', 'view_count']);
        });
    }
};
