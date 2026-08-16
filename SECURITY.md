# Security Policy

## Unterstützte Versionen

MovieShelf wird als Rolling Release betrieben: Sicherheitsupdates erhält ausschließlich die jeweils aktuelle Version, die auf movieshelf.info deployed ist.

| Version | Unterstützt |
| ------- | ----------- |
| Shelf 2.30.x (aktuell) | Ja |
| Ältere Versionen | Nein |

Selbst gehostete Instanzen sollten immer zeitnah auf den aktuellen Stand von `main` aktualisieren (`./update.sh`).

## Sicherheitslücke melden

Bitte melde Sicherheitslücken **nicht** über öffentliche GitHub-Issues.

- **E-Mail:** support@movieshelf.info (Betreff: "Security")
- Alternativ über GitHubs private Schwachstellen-Meldung: *Security → Report a vulnerability* in diesem Repository

Bitte beschreibe das Problem so konkret wie möglich (betroffene URL/Komponente, Schritte zur Reproduktion, mögliche Auswirkung). Ein Proof of Concept hilft, greife dabei aber bitte nicht auf fremde Daten zu — für Tests kann jederzeit ein eigenes kostenloses Filmregal registriert werden.

## Was du erwarten kannst

- **Eingangsbestätigung** innerhalb von 72 Stunden
- **Ersteinschätzung** (angenommen/abgelehnt, Schweregrad) innerhalb von 7 Tagen
- Bestätigte Lücken werden priorisiert behoben und mit dem nächsten Deploy veröffentlicht; du wirst über den Fix informiert
- Koordinierte Offenlegung: Bitte gib uns bis zu 90 Tage Zeit, bevor Details veröffentlicht werden

MovieShelf ist ein privates Open-Source-Projekt; ein Bug-Bounty-Programm gibt es nicht. Verantwortungsvoll gemeldete Lücken werden auf Wunsch im Release vermerkt (Credits).
