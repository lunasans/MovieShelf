<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lists', 'share_token')) {
            Schema::table('lists', function (Blueprint $table) {
                // Öffentlicher Read-only-Link: Token gesetzt = Liste geteilt
                $table->string('share_token', 64)->nullable()->unique()->after('name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
    }
};
