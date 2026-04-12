<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        EmailTemplate::updateOrCreate(['slug' => 'tenant_welcome'], [
            'name' => 'Willkommens-E-Mail (Registrierung)',
            'subject' => 'Willkommen bei deinem MovieShelf!',
            'content' => '<x-mail::message>
# Hallo {{ $user->name }}!

Dein persönliches MovieShelf wurde erfolgreich reserviert.

Bevor du loslegen kannst, musst du dein Filmregal über den folgenden Button freischalten:

<x-mail::button :url="$activationUrl">
Jetzt MovieShelf freischalten
</x-mail::button>

Nach der Aktivierung ist deine Sammlung unter:
[{{ $tenantUrl }}]({{ $tenantUrl }}) erreichbar.

Du kannst dich dann mit deinem Benutzernamen **{{ $user->username }}** und deinem Passwort anmelden.

Viel Spaß beim Verwalten deiner Filmsammlung!

Danke,<br>
{{ config(\'app.name\') }}
</x-mail::message>',
            'variables_hint' => '$user->name, $user->username, $activationUrl, $tenantUrl'
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'tenant_activated'], [
            'name' => 'Aktivierungs-E-Mail (Freischaltung)',
            'subject' => 'Dein MovieShelf ist jetzt aktiv!',
            'content' => '<x-mail::message>
# Hallo {{ $user->name }}!

Dein MovieShelf ist jetzt freigeschaltet und einsatzbereit.

Du kannst dich ab sofort unter folgendem Link anmelden:

<x-mail::button :url="$tenantUrl">
Zum MovieShelf
</x-mail::button>

Melde dich mit deinem Benutzernamen **{{ $user->username }}** und deinem Passwort an.

Viel Spaß beim Verwalten deiner Filmsammlung!

Danke,<br>
{{ config(\'app.name\') }}
</x-mail::message>',
            'variables_hint' => '$user->name, $user->username, $tenantUrl'
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'tenant_deletion_request'], [
            'name' => 'Bestätigung der Löschung',
            'subject' => 'WICHTIG: Bestätigung der Regal-Löschung ({{ $tenantName }})',
            'content' => 'Hallo,

du hast die Löschung deines MovieShelfs "{{ $tenantName }}" angefordert.

Um diesen Vorgang abzuschließen und alle Daten unwiderruflich zu löschen, klicke bitte auf den folgenden Link:

[{{ $deletionUrl }}]({{ $deletionUrl }})

Falls du diese Anfrage nicht gestellt hast, kannst du diese E-Mail einfach ignorieren.

Danke,
{{ config(\'app.name\') }}',
            'variables_hint' => '$tenantName, $deletionUrl'
        ]);
    }
}
