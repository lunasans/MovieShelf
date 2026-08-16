# Boxsets: drei Regeln, die sich ähnlich sehen

Stand: August 2026

Ein Boxset ist eine Zeile in `movies` wie jede andere — mit dem Unterschied,
dass andere Zeilen über `boxset_parent` auf sie zeigen. Für die Hülle und ihre
Teile gelten drei **verschiedene** Regeln, und sie zu verwechseln hat schon
mehrfach Fehler erzeugt. Deshalb hier einmal ausgeschrieben.

## 1. Listen zeigen die Hülle

Die Filmliste blendet die Teile aus: ein Boxset steht dort als **ein** Eintrag,
seine Filme stecken darin.

```php
// MovieController::index
if (! $hasFilters) {
    $query->whereNull('boxset_parent');
}
```

Bei einer Suche oder aktivem Filter fällt der Filter weg — sonst wäre ein
einzelner Film aus einem Boxset unauffindbar.

Die Zahl neben der Überschrift (`$movies->total()`) zählt entsprechend
Einträge, nicht Filme.

## 2. Kennzahlen zählen die Teile

Genau umgekehrt: Die Statistik wirft die Hülle weg und zählt die enthaltenen
Filme, denn das sind die Filme, die man besitzt.

```php
// StatsController
Movie::where('is_deleted', false)
    ->where('in_collection', true)
    ->whereDoesntHave('boxsetChildren')
    ->count();
```

Für Renes Sammlung (Stand 11.08.2026) ist der Unterschied greifbar:

| Zählweise | Anzahl |
|---|---|
| Listeneinträge (Hüllen, ohne Teile) | 548 Filme + 11 Serien |
| Besessene Filme (Teile, ohne Hüllen) | 591 Filme + 11 Serien |

Beides ist richtig — sie beantworten verschiedene Fragen. Wer sie vertauscht,
bekommt eine Liste, in der jede Sammlung in ihre Einzelteile zerfällt, oder
eine Statistik, die Hüllen als Filme zählt.

## 3. "Gesehen" wird aus den Teilen abgeleitet

Ein Boxset schaut niemand — man schaut die Filme darin. Die Hülle bekommt
deshalb nie eine eigene Markierung, und ohne Ableitung steht jede Sammlung für
immer als ungesehen da. In Renes Sammlung waren das ausnahmslos **alle** 15
Einträge, die als ungesehen galten: 15 Boxsets, bei denen jeder einzelne Teil
gesehen war.

```php
// Movie::isWatchedBy() — die eine Stelle, die das entscheidet
$movie->isWatchedBy(Auth::user());
```

Abgeleitet wird streng: gesehen erst, wenn **jeder** Teil gesehen ist. Ein halb
geschautes Boxset als "gesehen" auszuweisen wäre die unangenehmere Unwahrheit.

Der eigene Pivot-Eintrag der Hülle bleibt außen vor. Er stünde sonst als zweite
Wahrheit neben der Ableitung, und man sähe der Anzeige nicht mehr an, welche
gerade gilt.

Entsprechend wirkt das Umschalten (`MovieWatchedController::toggle`,
`Api\MovieController::toggleWatched`) bei einem Boxset auf **die Teile**: die
Hülle zu markieren bliebe ja wirkungslos.

## Wo dieselben Regeln noch gelten

Alle drei Clients halten sich daran; wer eine Regel ändert, sollte die anderen
mitdenken:

| Ort | Datei |
|---|---|
| Shelf (hier) | `app/Models/Movie.php`, `app/Http/Controllers/StatsController.php` |
| Android-App | `data/local/db/MovieDao.kt`, `data/repository/MovieRepository.kt` |
| Desktop-App | `electron/handlers/movies.ts` (`applyBoxsetWatched`, `listMovies`) |

## Warum das Setzen `updated_at` anfasst

Die Markierung lebt in `movie_user_watched`. Ein reines `attach()`/`detach()`
lässt `movies.updated_at` unberührt — der Delta-Export
(`AdminMovieController::export`) filtert aber genau darauf. Ein geänderter
Gesehen-Stand fiel deshalb aus jedem Delta-Abgleich heraus und erreichte
Desktop- und Android-App nie; nur ein Voll-Abgleich brachte ihn mit.

`Movie::setWatchedFor()` schiebt `updated_at` deshalb mit an. Das ist der
eigentliche Zweck der Methode, und beide Endpunkte gehen durch sie hindurch:

| Endpunkt | benutzt von |
|---|---|
| `MovieWatchedController::toggle` | Web-Oberfläche |
| `Api\MovieController::toggleWatched` | Android- und Desktop-App |

Folge, die man kennen sollte: Ein umgeschalteter Film taucht im nächsten
Delta-Export auf — bei einem Boxset entsprechend alle seine Teile. Genau das
ist beabsichtigt, denn nur so erfahren die Clients davon.

Episoden (`EpisodeWatchedController`) haben einen eigenen Weg und sind hier
nicht mitgeändert.
