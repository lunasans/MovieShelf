# Artisan-Commands

Alle projekteigenen Commands aus `app/Console/Commands/` sowie der Scheduler-Plan aus
`routes/console.php`. Standard-Laravel-Commands (`migrate`, `queue:work`, …) sind nicht aufgeführt.

Auf Prod immer im Projektverzeichnis ausführen, z. B.:

```
php artisan tenants:backup --tenant=meinregal
```

## Tenants & Betrieb

### `tenants:backup`
Erstellt `.ms`-Backups für alle aktivierten Tenants inkl. Aufbewahrung und optionalem S3-Upload.
Läuft nur, wenn `backup_auto_enabled` (Cadmin-Setting) gesetzt ist — außer mit `--force`.

| Option | Bedeutung |
| --- | --- |
| `--tenant=` | Nur diesen Tenant sichern (ID/Subdomain) |
| `--keep=` | Aufbewahrte Backups pro Tenant (Default: Setting `backup_keep_count`, sonst 7) |
| `--force` | Auch ausführen, wenn `backup_auto_enabled` deaktiviert ist |

### `tenants:backup:decrypt`
Entschlüsselt eine Off-site-Backup-Datei (`.ms.enc`) zurück in eine normale `.ms`-Datei.
Benötigt `BACKUP_ENCRYPTION_KEY`.

| Argument / Option | Bedeutung |
| --- | --- |
| `file` | Pfad zur `.ms.enc`-Datei (Pflicht) |
| `--output=` | Zielpfad für die entschlüsselte Datei (Default: gleicher Pfad ohne `.enc`) |

### `tenants:migrate-to-s3`
Migriert lokale Medien (`covers/`, `backdrops/`, `actors/`) nach Cloudflare R2/S3 —
für einen oder alle Tenants. Bereits vorhandene Dateien werden übersprungen.

| Argument | Bedeutung |
| --- | --- |
| `tenant` | Tenant-ID (optional; leer = alle Tenants) |

### `app:cleanup-unactivated-tenants`
Löscht Tenants, die nach X Tagen seit der Registrierung nie aktiviert wurden.

| Option | Bedeutung |
| --- | --- |
| `--days=` | Frist in Tagen (Default: Setting `tenant_unactivated_days`, sonst 10) |

### `app:warn-inactive-tenants`
Verschickt Inaktivitäts- bzw. Löschwarnungs-Mails an Tenants ohne Login.

| Option | Bedeutung |
| --- | --- |
| `--days=` | Mindest-Inaktivität in Tagen (Default: Setting `tenant_warn_days`, sonst 30) |
| `--max-days=` | Optionale Obergrenze (exklusiv) in Tagen |
| `--deletion-warning` | Löschwarnung statt allgemeiner Inaktivitäts-Mail senden |
| `--delete-after=` | Tage bis zur Löschung (Default: Setting `tenant_delete_days`, sonst 60) |

### `app:delete-inactive-tenants`
Löscht Tenants, die seit X Tagen keinen Login mehr hatten.

| Option | Bedeutung |
| --- | --- |
| `--days=` | Frist in Tagen (Default: Setting `tenant_delete_days`, sonst 60) |

### `storage:stats`
Erhebt die Speicherbelegung (lokal + R2) und cacht sie für die Cadmin-Übersicht.
Keine Optionen.

### `demo:reset`
Setzt das Demo-Regal auf den eingefrorenen Snapshot zurück.

| Option | Bedeutung |
| --- | --- |
| `--force` | Auch ausführen, wenn Auto-Reset aus ist oder `demo_reset_hours` noch nicht erreicht ist |

## Medien & Inhalte

### `movies:backfill-tmdb-images`
Lädt verbliebene TMDb-Cover/Backdrops herunter und legt sie auf die Upload-Disk (S3).

| Argument / Option | Bedeutung |
| --- | --- |
| `tenant` | Tenant-ID (optional; leer = alle aktivierten Tenants) |
| `--dry-run` | Nur anzeigen, was heruntergeladen würde |

### `movies:smart-trailer`
Sucht fehlende Film-Trailer über die TMDb-API.

| Option | Bedeutung |
| --- | --- |
| `--force` | Auch aktualisieren, wenn `trailer_url` bereits gefüllt ist |
| `--movie=` | Nur einen bestimmten Film (ID) |
| `--tenant=` | Nur einen bestimmten Tenant (ID) |

### `series:sync`
Gleicht Serien mit TMDb ab und informiert Follower über neue Episoden **bereits vorhandener**
Staffeln. Importiert nichts — keine neuen Staffeln, keine DB-Änderung am Bestand.

| Option | Bedeutung |
| --- | --- |
| `--tenant=` | Nur einen bestimmten Tenant (ID) |
| `--serie=` | Nur eine bestimmte Serie (Movie-ID) |
| `--dry-run` | Nur anzeigen, was erkannt würde (keine Mails) |

### `app:find-duplicate-movies`
Findet Filme mit gleichem Titel und Collection-Type.

| Option | Bedeutung |
| --- | --- |
| `--merge` | Duplikate automatisch zusammenführen |

### `app:merge-duplicate-actors`
Führt namensgleiche Schauspieler zusammen und hängt die Film-Relationen an den TMDb-Datensatz.
Keine Optionen.

## Benutzer

### `app:create-user`
Legt interaktiv einen neuen Benutzer an. Keine Optionen.

### `app:update-user`
Setzt das Passwort eines Benutzers neu.

| Argument | Bedeutung |
| --- | --- |
| `email` | E-Mail des Benutzers (optional; sonst interaktive Abfrage) |

## Migration / einmalige Aktionen

### `central:copy-from-sqlite`
Kopiert zentrale Daten aus einer alten SQLite-Datei in die aktuelle (MySQL-)Central-Verbindung.

| Option | Bedeutung |
| --- | --- |
| `--sqlite=` | Pfad zur alten zentralen SQLite-Datei (Default: `database/database.sqlite`) |
| `--force` | Ohne Rückfrage ausführen |

### `app:migrate-v1`
Migriert Daten aus der v1.5-MySQL-Datenbank in die v2.0-Datenbank.

| Option | Bedeutung |
| --- | --- |
| `--fresh` | Alle Tabellen vor der Migration leeren |

## Scheduler

Der System-Cron ruft jede Minute `php artisan schedule:run` auf. Geplante Jobs
(definiert in `routes/console.php`):

| Zeit | Command |
| --- | --- |
| jede Minute | Heartbeat — schreibt `scheduler_last_run_at` (Cron-Erkennung im Admin-Panel) |
| täglich 02:30 | `tenants:backup` |
| täglich 03:00 | `movies:smart-trailer` |
| täglich 03:30 | `storage:stats` |
| täglich 04:00 | `series:sync` |
| täglich | `app:cleanup-unactivated-tenants` |
| täglich | `app:delete-inactive-tenants --days={tenant_delete_days}` |
| stündlich | `demo:reset` (setzt nur zurück, wenn Auto-Reset aktiv und Intervall erreicht) |
| wöchentlich | `app:warn-inactive-tenants` (allgemeine Warnung) |
| wöchentlich | `app:warn-inactive-tenants --deletion-warning` (letzte ~10 Tage vor Löschung) |

Die Fristen der Lifecycle-Jobs kommen aus den Cadmin-Settings `tenant_warn_days` und
`tenant_delete_days`; die Lösch-Warnung startet 10 Tage vor der Löschung.
