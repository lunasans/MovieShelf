<x-mail::message>
# Neue Episoden verfügbar

Hallo {{ $user->name }},

zu der Serie **{{ $seriesTitle }}** aus deiner Sammlung
{{ $episodeCount === 1 ? 'ist eine neue Episode' : 'sind '.$episodeCount.' neue Episoden' }} erschienen:

{{ $episodeList }}

<x-mail::button :url="$seriesUrl">
Zur Serie
</x-mail::button>

Viel Spaß beim Weiterschauen!

{{ config('app.name') }}

@if(!empty($unsubscribeUrl))
<x-mail::subcopy>
Du möchtest keine E-Mails mehr zu neuen Episoden erhalten? [Hier abmelden]({{ $unsubscribeUrl }})
</x-mail::subcopy>
@endif
</x-mail::message>
