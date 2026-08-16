<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Gegenstück zu 2026_07_19_110000: Serien (collection_type = 'Serie'),
     * die noch tmdb_type = 'movie' tragen (Altlast des alten Desktop-Pushs,
     * der TV-IDs mit type=movie hochlud), werden auf 'tv' korrigiert.
     * Das gecachte tmdb_json stammt bei diesen Zeilen aus dem falschen
     * TMDb-Namensraum (Film mit derselben ID) und wird verworfen — das
     * nächste Mass-Update holt die korrekten Seriendaten.
     * updated_at wird mitgebumpt, damit Sync-Clients die Korrektur sehen.
     */
    public function up(): void
    {
        DB::table('movies')
            ->where('collection_type', 'Serie')
            ->where('tmdb_type', 'movie')
            ->update([
                'tmdb_type'  => 'tv',
                'tmdb_json'  => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Nicht sinnvoll umkehrbar.
    }
};
