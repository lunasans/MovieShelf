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
            $table->unsignedBigInteger('tmdb_id')->nullable()->unique()->after('id');
            $table->string('profile_path', 255)->nullable()->after('last_name');
            $table->date('birthday')->nullable()->after('birth_year');
            $table->date('deathday')->nullable()->after('birthday');
            $table->string('place_of_birth', 255)->nullable()->after('deathday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actors', function (Blueprint $table) {
            $table->dropColumn(['tmdb_id', 'profile_path', 'birthday', 'deathday', 'place_of_birth']);
        });
    }
};
