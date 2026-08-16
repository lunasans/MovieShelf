<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Uebersetzungen fuer redaktionelle Inhalte (FAQ, CMS-Seiten, Screenshots).
     * Der Basiswert bleibt in der Ursprungsspalte des Models — hier stehen nur
     * die abweichenden Sprachen. Fehlt ein Eintrag, faellt das Model auf die
     * Basisspalte zurueck, damit nie ein leeres Feld ausgeliefert wird.
     */
    public function up(): void
    {
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable');
            $table->string('locale', 10);
            $table->string('field', 64);
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'content_translations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_translations');
    }
};
