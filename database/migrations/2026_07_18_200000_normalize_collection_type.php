<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Kanonisches collection_type-Schema: Film | Serie.
     * Das Medium (DVD, Blu-ray, 4K, Streaming, ...) gehört ins tag-Feld.
     * Historische Format-Werte in collection_type (aus altem Import/Migrationen)
     * werden — sofern noch kein Tag gesetzt ist — in den Tag gerettet,
     * anschließend wird alles außer Serie auf Film normalisiert.
     */
    public function up(): void
    {
        $formatToTag = [
            'Blu-ray'   => 'BluRay',
            'BluRay'    => 'BluRay',
            'DVD'       => 'DVD',
            '4K'        => '4K',
            'Stream'    => 'Streaming',
            'Streaming' => 'Streaming',
            'Digital'   => 'Digital',
            'VHS'       => 'VHS',
        ];

        foreach ($formatToTag as $format => $tag) {
            DB::table('movies')
                ->where('collection_type', $format)
                ->where(function ($q) {
                    $q->whereNull('tag')->orWhere('tag', '');
                })
                ->update(['tag' => $tag]);
        }

        DB::table('movies')
            ->whereNotNull('collection_type')
            ->whereNotIn('collection_type', ['Film', 'Serie'])
            ->update(['collection_type' => 'Film']);
    }

    public function down(): void
    {
        // Datenbereinigung ist nicht umkehrbar (Originalwerte gehen verloren).
    }
};
