<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Physische Sammlungs-Metadaten pro Titel: Edition, Regionalcode, physischer
 * Standort im Regal sowie Kaufdatum/-preis und Zustand. Das Medium/Format
 * (DVD/Blu-ray/4K …) steckt bereits in der Spalte `tag`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('movies')) {
            return;
        }

        Schema::table('movies', function (Blueprint $table) {
            if (! Schema::hasColumn('movies', 'edition')) {
                $table->string('edition', 120)->nullable();
            }
            if (! Schema::hasColumn('movies', 'region_code')) {
                $table->string('region_code', 20)->nullable();
            }
            if (! Schema::hasColumn('movies', 'disc_location')) {
                $table->string('disc_location', 120)->nullable();
            }
            if (! Schema::hasColumn('movies', 'purchase_date')) {
                $table->date('purchase_date')->nullable();
            }
            if (! Schema::hasColumn('movies', 'purchase_price')) {
                $table->decimal('purchase_price', 8, 2)->nullable();
            }
            if (! Schema::hasColumn('movies', 'condition')) {
                $table->string('condition', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('movies')) {
            return;
        }

        Schema::table('movies', function (Blueprint $table) {
            foreach (['edition', 'region_code', 'disc_location', 'purchase_date', 'purchase_price', 'condition'] as $col) {
                if (Schema::hasColumn('movies', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
