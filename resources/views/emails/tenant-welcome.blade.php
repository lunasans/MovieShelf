<x-mail::message>
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
{{ config('app.name') }}
</x-mail::message>
