<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bot_runs', function (Blueprint $table) {
            $table->unsignedBigInteger('last_actor_id')->default(0)->after('processed_actors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bot_runs', function (Blueprint $table) {
            $table->dropColumn('last_actor_id');
        });
    }
};
