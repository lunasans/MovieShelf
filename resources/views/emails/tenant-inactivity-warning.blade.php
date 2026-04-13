<x-mail::message>
# Alles gut bei dir?

Wir haben bemerkt, dass dein MovieShelf seit **{{ $inactiveDays }} Tagen** nicht besucht wurde.

Deine Filmsammlung wartet auf dich – vielleicht ist es Zeit für einen neuen Filmabend?

<x-mail::button :url="$loginUrl">
Zum MovieShelf
</x-mail::button>

Falls du Fragen oder Probleme hast, melde dich einfach bei uns.

Bis bald,<br>
{{ config('app.name') }}
</x-mail::message>
