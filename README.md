![Logo](https://neuhaus.ovh/img/logo/logo.png)
# MovieShelf - Dein digitales Filmregal


Ein modernes, webbasiertes Tool zur Verwaltung Ihrer privaten Filmsammlung mit eleganter Benutzeroberfläche und umfangreichen Funktionen.

## Banner
[![Signature](https://neuhaus.ovh/signature?type=1)](https://neuhaus.ovh)

[![Signature](https://neuhaus.ovh/signature?type=2)](https://neuhaus.ovh)

[![Signature](https://neuhaus.ovh/signature?type=3)](https://neuhaus.ovh)

## 🎬 Übersicht

MovieShelf ist eine vollständige Webanwendung zur Verwaltung, Durchsuchung und Präsentation Ihrer DVD/Blu-ray-Sammlung. Das System bietet eine intuitive Benutzeroberfläche mit Glass-Morphism-Design und umfangreiche Funktionen für Film-Enthusiasten.

## ✨ Hauptfunktionen

### 📥 Import & Datenmanagement
- **XML-Import** aus collection.xml (kompatibel mit DVD Profiler)
- **TMDb-Import** - Filme und Serien direkt über TMDb API importieren
- **Automatischer Datenbankabgleich** mit Update- und Einfügefunktionen
- **BoxSet-Erkennung** mit gruppierten, aufklappbaren Unterfilmen

### 🎭 Film-Details & Präsentation
- **Umfassende Film-Informationen** mit Schauspielern, Cover und Übersicht
- **Trailer-Integration** für erweiterte Filminformationen
- **Responsive Design** für alle Bildschirmgrößen
- **Listen- und Kachelansicht** mit nahtlosem Umschalten

### 🎭 Schauspieler-Profile
- **Detaillierte Schauspieler-Profile** mit Biografien und Fotos
- **Filmografie-Übersicht** für jeden Schauspieler
- **Verknüpfung Film ↔ Schauspieler** mit Rolleninformationen
- **Inline-Editing** für schnelle Aktualisierungen

###  Benutzer-Features
- **Persönliche Bewertungen** für Filme
- **"Gesehen"-Status** zum Tracking
- **Wunschliste** für zukünftige Filme
- **Aktivitäts-Log** zur Nachverfolgung

### 📊 Erweiterte Features
- **Statistikseite** mit interaktiven Diagrammen (Chart.js)
- **Admin-Panel** mit umfangreichen Verwaltungsfunktionen
- **Besucherzähler** für Nutzungsstatistiken
- **Foren-Signaturbanner** - Dynamische Banner mit neuesten Filmen
- **2FA-Authentifizierung** für erhöhte Sicherheit
- **DSGVO-konformes Design** mit Impressum und Datenschutz

## 🛠️ Technische Details

### Systemanforderungen
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL/MariaDB oder SQLite
- Webserver (Apache/Nginx)

### Verwendete Technologien
- **Backend**: Laravel Framework (PHP)
- **Frontend**: Blade Templates, Tailwind CSS (via Vite), JavaScript
- **UI-Bibliotheken**: 
  - Bootstrap Icons
  - Fancybox für Lightbox-Funktionen
  - Chart.js für Statistiken
- **Datenbank**: MySQL/MariaDB oder SQLite
- **APIs**: TMDb API für Film-Metadaten

## 📁 Projektstruktur

```text
movieshelf/
├── app/                    # Laravel Core (Controller, Models, etc.)
├── bootstrap/              # System-Initialisierung
├── config/                 # Konfigurationsdateien
├── database/               # Migrationen und Seeder
├── public/                 # Öffentliches Verzeichnis (Assets, index.php)
├── resources/              # Ansichten (Blade) und unkompilierte Assets
│   ├── css/               # Tailwind CSS
│   ├── js/                # JavaScript
│   └── views/             # Blade Templates
├── routes/                 # Web- und API-Routen
├── storage/                # Logs, Caches, hochgeladene Dateien
├── tests/                  # Automatisierte Tests
└── README.md               # Diese Datei
```

## 🐳 Installation mit Docker

Der schnellste Weg zu einer eigenen Instanz. Ein Container, SQLite, keine
weiteren Dienste - Queue-Worker und Scheduler laufen mit im Container.

```bash
curl -O https://raw.githubusercontent.com/lunasans/MovieShelf/main/docker-compose.yml
docker compose up -d
```

Danach läuft MovieShelf auf <http://localhost:8080>. Der erste registrierte
Account wird zum Administrator.

### Konfiguration

Alle Einstellungen kommen als Umgebungsvariablen aus der `docker-compose.yml` -
im Container gibt es keine `.env`-Datei. Die wichtigsten:

| Variable | Default | Bedeutung |
|---|---|---|
| `APP_URL` | `http://localhost:8080` | Öffentliche Adresse. **Muss** gesetzt werden, sonst zeigen Links und Mails auf localhost. |
| `APP_KEY` | wird generiert | Beim ersten Start erzeugt und in `storage/app/app_key` abgelegt. Nur setzen, wenn du ihn selbst verwalten willst. |
| `TZ` | `UTC` | Zeitzone für Scheduler und Anzeige. |
| `DB_CONNECTION` | `sqlite` | `mysql` für die MariaDB-Variante. |
| `TRUSTED_PROXIES` | leer | IP/CIDR des Reverse-Proxys. Niemals `*` - damit wären `X-Forwarded-For` und alle IP-Rate-Limits fälschbar. |
| `RUN_QUEUE_WORKER` | `true` | Queue-Worker im Container starten. |
| `RUN_SCHEDULER` | `true` | Laravel-Scheduler im Container starten (ersetzt den System-Cron). |
| `RUN_MIGRATIONS` | `true` | Migrationen beim Start ausführen. |

### Daten und Backup

Zwei Volumes halten alles Persistente:

- `movieshelf-storage` → `/var/www/html/storage` - Cover, Uploads, Backups, Logs, `APP_KEY`
- `movieshelf-database` → `/var/www/html/database` - die SQLite-Datenbank

Beide sichern, dann ist die Installation vollständig gesichert.

### Updates

Für Docker-Installationen ersetzt das Image das [`update.sh`](update.sh)
komplett - Migrationen laufen beim Start automatisch:

```bash
docker compose pull
docker compose up -d
```

### Hinter einem Reverse-Proxy

Der Container spricht nur HTTP auf Port 8080; TLS und Security-Header gehören
in den Proxy davor. `APP_URL` auf die öffentliche HTTPS-Adresse setzen und
`TRUSTED_PROXIES` auf das Netz des Proxys.

### MariaDB statt SQLite

Sinnvoll ab mehreren tausend Titeln oder mehreren parallelen Nutzern, weil
SQLite jeden Schreibzugriff serialisiert:

```bash
docker compose -f docker-compose.yml -f docker-compose.mariadb.yml up -d
```

Vorher die Passwörter in `docker-compose.mariadb.yml` ändern. Ein Wechsel
migriert **keine** Daten - vorher im Admin-Panel exportieren und danach wieder
importieren.

### Verfügbare Images

Bei jedem Release wird dasselbe Image nach beiden Registries gepusht -
`linux/amd64` und `linux/arm64` (NAS, Raspberry Pi, Apple Silicon):

```
tessaa/movieshelf:latest            # Docker Hub
ghcr.io/lunasans/movieshelf:latest  # GitHub Container Registry
```

Beide sind dasselbe Image mit demselben Digest. Die `docker-compose.yml` nutzt
Docker Hub; die GHCR-Zeile steht dort auskommentiert daneben.

### Selbst bauen

```bash
git clone https://github.com/lunasans/MovieShelf.git
cd MovieShelf
docker build -t movieshelf .
```

---

## 🚀 Installation (ohne Docker)

### 1. Repository klonen & Abhängigkeiten installieren
```bash
git clone https://github.com/lunasans/MovieShelf.git  
cd MovieShelf
composer install
npm install
```

### 2. Konfiguration & Datenbank
- Kopieren Sie die `.env.example` zu `.env`:
  ```bash
  cp .env.example .env
  ```
- Generieren Sie den Application-Key:
  ```bash
  php artisan key:generate
  ```
- Konfigurieren Sie in der `.env` Datei Ihre Datenbankverbindung (z.B. SQLite oder MySQL).
- Führen Sie die Datenbank-Migrationen aus:
  ```bash
  php artisan migrate
  ```

### 3. Assets kompilieren
```bash
npm run build
```

### 4. Server starten
Wenn Sie keinen lokalen Webserver (wie Apache/Nginx oder Laravel Valet) nutzen, können Sie den eingebauten Server verwenden:
```bash
php artisan serve
```

### 5. XML-Import (Optional)
- Exportieren Sie Ihre Sammlung aus DVD Profiler als `collection.xml`
- Nutzen Sie die Import-Funktion im Admin-Panel zur Migration der Sammlung.

## 🎨 Features im Detail

### Glass-Morphism Design
Das moderne Interface nutzt Glasmorphismus-Effekte für eine elegante und zeitgemäße Benutzeroberfläche mit:
- Transparente Hintergründe mit Blur-Effekten
- Smooth Animationen und Hover-Effekte
- Responsive Grid-Layout
- Dunkler Modus verfügbar

### Erweiterte Suchfunktionen
- Volltext-Suche durch alle Film-Metadaten
- Filter nach Genre, Jahr, Bewertung
- Sortierung nach verschiedenen Kriterien
- Schnelle Navigation durch große Sammlungen

### Admin-Funktionen
- **Benutzer-Authentifizierung** mit 2FA-Unterstützung
- **Schauspieler-Verwaltung** mit Profil-Editor
- **Film-Verwaltung** mit TMDb-Import
- **Batch-Import** von XML-Dateien
- **Film-Metadaten Verwaltung** (z.B. fehlende Cover & TMDb-IDs)
- **Actor Bot** (Automatischer Background-Service zur Vervollständigung von Schauspieler-Profilen via TMDb)
- **Statistik-Dashboard**
- **GitHub-basierte System-Updates**

## 📊 Screenshots

Die Anwendung bietet eine moderne, benutzerfreundliche Oberfläche:
- **Hauptansicht**: Übersichtliche Film-Grid mit Cover-Bildern
- **Detail-Panel**: Ausführliche Informationen zu jedem Film
- **Statistiken**: Interaktive Diagramme Ihrer Sammlung
- **Admin-Panel**: Verwaltungstools für Power-User

## 🔒 Datenschutz & Sicherheit

- **DSGVO-konform**: Vollständige Datenschutzerklärung und Impressum
- **Keine externe Datenübertragung**: Alle Daten bleiben auf Ihrem Server
- **2FA-Authentifizierung**: Zwei-Faktor-Authentifizierung mit Backup-Codes
- **Sichere Sessions**: IP-Subnet-Validierung und User-Agent-Checks
- **Content Security Policy**: Schutz vor XSS-Angriffen
- **CSRF-Protection**: Schutz vor Cross-Site-Request-Forgery
- **Prepared Statements**: SQL-Injection-Schutz
- **Password Hashing**: Bcrypt-Verschlüsselung

## 📋 Änderungen

Alle Versionen und ihre Änderungen stehen bei den
[Releases](https://github.com/lunasans/MovieShelf/releases) und in den
[Tags](https://github.com/lunasans/MovieShelf/tags).

## 🤝 Mitwirken

Beiträge sind willkommen! Bitte:
1. Forken Sie das Repository
2. Erstellen Sie einen Feature-Branch
3. Committen Sie Ihre Änderungen
4. Erstellen Sie einen Pull Request

## 📝 Lizenz

Dieses Projekt ist für den privaten Gebrauch konzipiert. Weitere Details finden Sie in der LICENSE-Datei.

## 👤 Autor

**René Neuhaus**  
GitHub: [@lunasans](https://github.com/lunasans)

## 🐛 Support & Feedback

Bei Fragen, Problemen oder Verbesserungsvorschlägen:
- Erstellen Sie ein [GitHub Issue](https://github.com/lunasans/MovieShelf/issues)
- Nutzen Sie die Diskussionsfunktion im Repository


**Status**: Aktiv entwickelt

*Verwalten Sie Ihre Filmsammlung mit Stil und Effizienz!* 🎬✨
