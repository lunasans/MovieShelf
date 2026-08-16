<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Zusätzliche Episoden-Felder aus TMDb (Laufzeit, Ausstrahlungsdatum)
        Schema::table('episodes', function (Blueprint $table) {
            if (! Schema::hasColumn('episodes', 'runtime')) {
                $table->integer('runtime')->nullable()->after('overview');
            }
            if (! Schema::hasColumn('episodes', 'air_date')) {
                $table->date('air_date')->nullable()->after('runtime');
            }
        });

        // Gesehen-Status pro Episode und Nutzer
        if (! Schema::hasTable('episode_user_watched')) {
            Schema::create('episode_user_watched', function (Blueprint $table) {
                $table->foreignId('episode_id')->constrained('episodes')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('watched_at')->nullable();
                $table->primary(['episode_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('episode_user_watched');

        Schema::table('episodes', function (Blueprint $table) {
            if (Schema::hasColumn('episodes', 'air_date')) {
                $table->dropColumn('air_date');
            }
            if (Schema::hasColumn('episodes', 'runtime')) {
                $table->dropColumn('runtime');
            }
        });
    }
};
