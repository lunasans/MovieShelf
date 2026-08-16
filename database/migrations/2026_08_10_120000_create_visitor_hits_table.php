<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Haelt fest, welche IP an welchem Tag bereits gezaehlt wurde.
 *
 * Zuvor lag diese Information im Cache. Schlaegt ein Cache-Schreibvorgang
 * fehl, meldet Laravel das nicht - die Eindeutigkeitspruefung faellt dann
 * unbemerkt aus und jeder einzelne Seitenaufruf zaehlt als eigener Besucher.
 * Genau das ist in der Produktion passiert. Ein Unique-Index kann nicht
 * stillschweigend ausfallen: Entweder die Zeile entsteht, oder der Treffer
 * war bereits vorhanden.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visitor_hits')) {
            Schema::create('visitor_hits', function (Blueprint $table) {
                $table->id();
                $table->date('visit_date');
                $table->string('ip_hash', 64);
                $table->timestamp('created_at')->nullable();

                $table->unique(['visit_date', 'ip_hash'], 'visitor_hits_day_ip_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_hits');
    }
};
