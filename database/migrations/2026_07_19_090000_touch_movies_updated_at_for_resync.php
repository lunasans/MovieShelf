<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Die Typ-Normalisierung (2026_07_18_200000) hat collection_type/tag per
     * Query-Builder geändert, ohne updated_at zu bumpen — Delta-Sync-Clients
     * (Desktop-App) haben die Korrekturen deshalb nie zu sehen bekommen und
     * liefen still auseinander. Welche Zeilen betroffen waren, ist im
     * Nachhinein nicht mehr feststellbar.
     *
     * Einmaliger Touch aller Filme: Beim nächsten (Delta-)Sync ziehen alle
     * Clients den kompletten Master-Stand neu. Lokal unveränderte Datensätze
     * werden überschrieben, lokal geänderte laufen wie üblich durch die
     * Konfliktauflösung.
     */
    public function up(): void
    {
        DB::table('movies')->update(['updated_at' => now()]);
    }

    public function down(): void
    {
        // Nicht umkehrbar (ursprüngliche Zeitstempel sind verloren) und
        // folgenlos für die Daten selbst.
    }
};
