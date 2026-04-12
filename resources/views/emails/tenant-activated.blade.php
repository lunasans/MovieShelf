<x-mail::message>
# Hallo {{ $user->name }}!

Dein MovieShelf ist jetzt freigeschaltet und einsatzbereit.

Du kannst dich ab sofort unter folgendem Link anmelden:

<x-mail::button :url="$tenantUrl">
Zum MovieShelf
</x-mail::button>

Melde dich mit deinem Benutzernamen **{{ $user->username }}** und deinem Passwort an.

Viel Spaß beim Verwalten deiner Filmsammlung!

Danke,<br>
{{ config('app.name') }}
</x-mail::message>
