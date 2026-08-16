<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * last_visit war als date angelegt, bekommt aber ein now() mit Uhrzeit
 * zugewiesen - die Uhrzeit fiel beim Speichern weg.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('counter')) {
            return;
        }

        Schema::table('counter', function (Blueprint $table) {
            $table->dateTime('last_visit')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('counter')) {
            return;
        }

        Schema::table('counter', function (Blueprint $table) {
            $table->date('last_visit')->nullable()->change();
        });
    }
};
