<x-mail::message>
# Wichtig: Dein MovieShelf wird gelöscht!

Dein MovieShelf wurde seit **{{ $inactiveDays }} Tagen** nicht mehr genutzt und wird in **{{ $daysUntilDeletion }} Tagen** automatisch gelöscht.

Melde dich einmal an, um dein MovieShelf zu behalten:

<x-mail::button :url="$loginUrl">
Jetzt anmelden & Shelf behalten
</x-mail::button>

Falls du dein MovieShelf nicht mehr benötigst, musst du nichts tun – es wird automatisch entfernt.

Danke,<br>
{{ config('app.name') }}
</x-mail::message>
