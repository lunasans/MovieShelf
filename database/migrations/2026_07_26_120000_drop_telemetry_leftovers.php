<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Die Telemetrie wurde vollstaendig entfernt (Service, Job, Scheduler-Eintrag,
 * Admin-Schalter und die Master-seitige Auswertung). Hier verschwinden die
 * Reste in der Datenbank: die Empfangstabelle und die beiden Setting-Zeilen.
 *
 * Die urspruengliche Create-Migration bleibt bewusst stehen – sie ist auf
 * bestehenden Regalen bereits gelaufen und gehoert zur Historie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('external_installations');

        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->whereIn('key', ['telemetry_enabled', 'telemetry_id'])
                ->delete();
        }
    }

    public function down(): void
    {
        // Die Setting-Zeilen sind nicht rekonstruierbar; die Tabelle legen wir
        // in ihrer urspruenglichen Form wieder an, damit ein Rollback sauber ist.
        if (! Schema::hasTable('external_installations')) {
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
    }
};
