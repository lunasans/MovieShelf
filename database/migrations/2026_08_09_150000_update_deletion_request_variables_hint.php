<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Der Variablen-Hinweis der Loesch-Vorlage nennt jetzt auch $user->name.
     *
     * Die Mail liefert den Nutzer inzwischen mit; ohne aktualisierten Hinweis
     * waere im Editor weiterhin zu lesen, dass es ihn nicht gibt.
     */
    public function up(): void
    {
        DB::table('email_templates')
            ->where('slug', 'tenant_deletion_request')
            ->where('variables_hint', 'not like', '%$user->name%')
            ->update(['variables_hint' => '$user->name, $tenantName, $deletionUrl']);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->where('slug', 'tenant_deletion_request')
            ->update(['variables_hint' => '$tenantName, $deletionUrl']);
    }
};
