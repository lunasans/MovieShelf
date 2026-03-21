![Logo](https://neuhaus.ovh/img/logo/logo.png)


# MovieShelf v2.1.1 - Dein digitales Filmregal



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

## 🚀 Installation

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


**Version**: 2.1.1 - Laravel Framework Edition

**Letztes Update**: März 2026  
**Status**: Aktiv entwickelt

*Verwalten Sie Ihre Filmsammlung mit Stil und Effizienz!* 🎬✨
