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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'activation_token')) {
                $table->string('activation_token', 64)->nullable()->after('email');
            }
            if (!Schema::hasColumn('tenants', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('activation_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['activation_token', 'activated_at']);
        });
    }
};
