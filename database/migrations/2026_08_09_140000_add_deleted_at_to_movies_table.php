<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Zeitpunkt der Sperrung, damit movies:purge eine belastbare Frist hat.
     *
     * updated_at genuegt dafuer nicht: eine Massenaktion (z. B. Genre setzen)
     * wuerde die Uhr zuruecksetzen, und beim Wiederherstellen und erneuten
     * Sperren waere nicht mehr erkennbar, wie lange ein Film tatsaechlich
     * gesperrt ist.
     */
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable()->after('is_deleted');
            $table->index(['is_deleted', 'deleted_at'], 'idx_movies_deleted');
        });

        // Bestandsdaten: bereits gesperrte Filme bekommen updated_at als
        // Naeherung, sonst wuerden sie nie aufgeraeumt.
        DB::table('movies')
            ->where('is_deleted', true)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex('idx_movies_deleted');
            $table->dropColumn('deleted_at');
        });
    }
};
