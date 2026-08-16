# Release-Flow & Forum-Ankündigungen

Stand: Juli 2026 · Workflow: [`forum-release-announcement.yml`](../.github/workflows/forum-release-announcement.yml)

## Überblick

Nutzerrelevante Releases (Shelf, Desktop-App, Android-App) werden automatisch als
Diskussion im Support-Forum (https://forum.movieshelf.info) angekündigt.
Interne Central-/SaaS-Änderungen (Landingpage, Cadmin, Infrastruktur) werden
**nicht** angekündigt.

Es gibt zwei Versionsstränge (`config/app.php`):

| Version | Betrifft | Forum-Ankündigung |
|---|---|---|
| `shelf_version` (z. B. 2.21.2) | Tenant-App („das Regal") | ja, via Tag `shelf-v*` |
| `saas_version` (z. B. 1.7.2) | Landingpage, Central, Cadmin | nein |

Desktop- und Android-App liegen in eigenen Repos und bekommen dort bei Bedarf
eine eigene Kopie des Workflows (ohne `shelf-v`-Filter).

## Shelf-Release veröffentlichen

### Standardweg: Tag pushen (vollautomatisch)

```bash
git tag shelf-v2.22.0
git push origin shelf-v2.22.0
```

Der Workflow erledigt dann:

1. GitHub-Release wird erstellt, Release Notes werden automatisch aus den
   Commit-Messages seit dem letzten Release generiert
2. Forum-Diskussion „MovieShelf v2.22.0" wird erstellt — in den Tags
   „Release Ankündigungen" + „Shelf", gepostet vom Bot-Account `GitHub_Action`

**Achtung:** Die auto-generierten Notes enthalten *alle* Commits seit dem letzten
Release — in diesem Mono-Repo also auch SaaS-Commits. Für kuratierte
Ankündigungen den manuellen Weg nehmen.

### Manueller Weg: kuratierte Release Notes

1. GitHub → Releases → **Draft a new release**
2. Tag mit Präfix `shelf-v` anlegen (z. B. `shelf-v2.22.0`)
3. **Generate release notes** klicken, Text bearbeiten/kürzen
4. **Publish release**

Der Workflow erkennt das bereits existierende Release und postet nur die
Forum-Ankündigung (keine Dublette).

### SaaS-/Central-Release (ohne Ankündigung)

Tags ohne `shelf-v`-Präfix (z. B. `v1.7.2` oder `saas-v1.7.2`) lösen keine
Forum-Ankündigung aus. GitHub-Releases dafür sind optional.

## Konfiguration

GitHub-Repo → Settings → Secrets and variables → Actions:

| Typ | Name | Wert |
|---|---|---|
| Secret | `FORUM_API_KEY` | Flarum-API-Key, in der Forum-DB (`api_keys.user_id`) fest an den Bot-Account `GitHub_Action` (User 2) gebunden |
| Variable | `FORUM_TAG_ID` | `2,3` — Flarum-Tag-IDs kommagetrennt (Oberthema „Release Ankündigungen" = 2, Unterthema „Shelf" = 3) |

Die Tag-IDs lassen sich über `https://forum.movieshelf.info/api/tags` nachschlagen.

Hinweis: Dieser Key ist **nicht** derselbe wie der `FORUM_API_KEY` in der
Server-`.env` der Laravel-App — der dortige Key ist an das Admin-Konto gebunden
und wird für die automatische Konto-Anlage bei der Shelf-Registrierung genutzt
(`App\Support\ForumAccount`).

## Troubleshooting

- Lauf ansehen: GitHub → Actions → „Shelf-Release & Forum-Ankündigung"
- **401 vom Forum**: API-Key im Secret prüfen (existiert der Key noch in der
  `api_keys`-Tabelle des Forums?)
- **422 vom Forum**: Tag-Anforderungen — `FORUM_TAG_ID` muss bei Unterthemen
  beide IDs enthalten; der Bot braucht das Recht, im Ziel-Tag Diskussionen zu
  starten
- **Kein Lauf gestartet**: Tag-Präfix prüfen — nur `shelf-v*` triggert
- Falsches Release erwischt? Aufräumen mit:
  `gh release delete <tag> --cleanup-tag --yes` und die Forum-Diskussion über
  das Moderations-Menü löschen
