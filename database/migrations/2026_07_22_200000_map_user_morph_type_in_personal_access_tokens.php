<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seit der Morph-Map aus dem Listen-Split schreibt Sanctum neue Tokens mit
     * tokenable_type = 'user'. Alt-Tokens tragen noch den vollen Klassennamen;
     * sie würden zwar weiter authentifizieren (Klassennamen-Fallback beim Lesen),
     * aber von $user->tokens() (Logout, Token-Verwaltung) nicht mehr gefunden.
     * Daher auf den Map-Alias vereinheitlichen.
     */
    public function up(): void
    {
        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\Models\User')
            ->update(['tokenable_type' => 'user']);
    }

    public function down(): void
    {
        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'user')
            ->update(['tokenable_type' => 'App\Models\User']);
    }
};
