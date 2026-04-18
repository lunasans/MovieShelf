<x-mail::message>
# Passwort zurücksetzen

Hallo {{ $user->name }},

du hast eine Anfrage zum Zurücksetzen deines Passworts gestellt.

<x-mail::button :url="$resetUrl">
Passwort zurücksetzen
</x-mail::button>

Dieser Link ist **60 Minuten** gültig.

Falls du keine Passwortzurücksetzung angefordert hast, kannst du diese E-Mail ignorieren.

Danke,
{{ config('app.name') }}
</x-mail::message>
