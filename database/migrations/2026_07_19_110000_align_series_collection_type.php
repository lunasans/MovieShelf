<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Datenhygiene zum Serien-Diskriminator: collection_type = 'Serie' ist
     * die kanonische Kennzeichnung. Zeilen, die per TMDb als Serie bekannt
     * sind (tmdb_type = 'tv'), aber einen abweichenden collection_type
     * tragen, werden angeglichen. updated_at wird mitgebumpt, damit
     * Sync-Clients die Korrektur unabhängig von der Migrations-Reihenfolge
     * mitbekommen.
     */
    public function up(): void
    {
        DB::table('movies')
            ->where('tmdb_type', 'tv')
            ->where(function ($q) {
                $q->whereNull('collection_type')->orWhere('collection_type', '!=', 'Serie');
            })
            ->update(['collection_type' => 'Serie', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Nicht sinnvoll umkehrbar.
    }
};
