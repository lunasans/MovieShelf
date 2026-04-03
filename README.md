![Logo](https://neuhaus.ovh/img/logo/logo.png)
# MovieShelf v2.6.1 - Dein digitales Filmregal


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

## 🚀 Letzte Änderungen (v2.6.1)

- **Fixed**: 2FA Backup-Codes werden nach Verwendung korrekt verbraucht und die Anzahl im Profil aktualisiert.
- **Improved**: OTP- und Backup-Code-Eingabe nutzen nun separate Formulare für zuverlässigere Verarbeitung.

## 🚀 Version v2.6.0 (Historie)

- **Cinematic Red Login Redesign**: Vollständige Neugestaltung der Login-Seite. Die Seite ist jetzt in das App-Layout integriert (wie die 2FA-Seite) mit dunklem cineastischem Hintergrund.
- **2FA Backup-Codes**: 8 einmalige Recovery-Codes werden bei der 2FA-Aktivierung generiert. Codes können im Profil angezeigt und regeneriert werden. Bei der 2FA-Challenge kann zwischen OTP und Backup-Code umgeschaltet werden.
- **Branding Alignment**: Umstellung von Blau/Violett auf das markenkonforme Rose/Crimson-Farbschema (Streaming-Layout).
- **Premium Glassmorphism**: Vertiefte Blur-Effekte und modernisierte Eingabefelder für ein nahtloses Benutzererlebnis ab der Anmeldung.

## 🚀 Version v2.5.9 (Historie)

- **Fixed**: Die öffentliche Schauspieler-Suche wurde für "Vorname Nachname" Kombinationen optimiert (z.B. "Eddie Murphy").

## 🚀 Version v2.5.8 (Historie)

- **Fixed**: Die öffentliche Schauspieler-Suche wurde für "Vorname Nachname" Kombinationen optimiert (z.B. "Eddie Murphy").

## 🚀 Version v2.5.7 (Historie)

- **Improved**: Der ActorBot validiert nun lokale Zuordnungen gegen die offiziellen TMDb-Credits.
- **Feature**: Automatischer Pruning-Prozess zur Bereinigung fehlerhafter Film-Schauspieler-Verknüpfungen.

## 🚀 Version v2.5.6 (Historie)

- **Improved**: Die System-Update-Seite im Admin-Bereich zeigt nun wieder den aktuellen Commit-Hash und die letzten 5 Änderungen aus dem Git-Log an.
- **Visual**: Optimiertes Layout der Update-Ansicht für bessere Übersicht über den Systemstatus.

## 🚀 Version v2.5.4 (Historie)

- **Fixed**: Massive Bereinigung fehlerhafter Schauspieler-Zuweisungen.
- **Improved**: Der Actor-Bot wurde durch einen Namensvalidierungs-Check abgesichert, um falsche Merges und IDs in Zukunft zu verhindern.

## 🚀 Version v2.5.3 (Historie)

- **Cleanup**: Der Theme-Switcher wurde aus dem Admin-Bereich entfernt, da das Design nun fest auf "Cinematic Red" optimiert ist.
- **Improved**: Weitere Optimierungen am Footer-Layout (TMDB Disclaimer & Abstände).

## 🚀 Version v2.5.2 (Historie)

- **New Feature**: Overhauled the Admin & Public Footer with a modern "Cinematic" design.
- **Improved**: Global styling for Select/Dropdown menus (no more white backgrounds in Dark Mode).
- **Fixed**: Restored missing Biography editor (Quill) on Actor Create/Edit pages.
- **Fixed**: Corrected layout spacing in the Admin Panel for better glassmorphism visibility.

## 🚀 Version v2.5.1 (Historie)

- **Cinematic Admin Redesign (Final)**: Umstellung des gesamten Administrationsbereichs auf das neue "Cinematic Red" Farbschema (Rose/Crimson).
- **Glassmorphism everywhere**: Konsistente Anwendung von tiefem `backdrop-blur` und Premium-Glass-Effekten auf alle Admin-Module (Migration, Bot, Update, Import).
- **Sleek Stats & Charts**: Die Besucherstatistiken wurden mit einem neuen, markenkonformen Chart-Design und modernisierten Infokarten ausgestattet.
- **Harmonized Components**: Alle Buttons, Badges, Inputs und Modals im Admin-Panel folgen nun der hochwertigen Ästhetik der öffentlichen Seite.
- **Improved UX**: Optimierte Abstände und Typografie in den Actor- und Movie-Bearbeitungsmasken für einen effizienteren Workflow.

## 🚀 Version v2.5.0 (Historie)

- **Admin Panel UI Overhaul**: Grundlegende Neugestaltung des Admin-Dashboards und der Listenansichten (Filme, Stars, Benutzer).
- **Themed Quill Editor**: Integration des Redesigns in den Rich-Text-Editor zur nahtlosen Bearbeitung im neuen Look.
- **Global Layout Modernization**: Einführung der neuen Sidebar-Struktur mit Glow-Effekten und optimierter Navigation.

## 🚀 Version v2.4.1 (Historie)

- **Quill Formatting Recovery**: Vollständige Wiederherstellung aller Editor-Formatierungen (Fett, Kursiv, Listen, Ausrichtung, Textgrößen) in der Detailansicht.
- **Smart Line Break Handling**: Einführung von `white-space: pre-wrap` um sicherzustellen, dass sowohl HTML-Absätze als auch einfache Zeilenumbrüche (Plain Text) korrekt dargestellt werden.
- **Improved Readability**: Entfernung von unnötigen Italic-Styles in den Detail-Texten für eine klarere und modernere Typografie.
- **Consistency Fixes**: Angleichung der Storyline-Darstellung zwischen Streaming- und Classic-Layout für ein einheitliches Benutzererlebnis.

## 🚀 Version v2.4.0 (Historie)

- **Manual Backdrop & Cover Upload**: Administratoren können nun eigene Backdrop- und Cover-Bilder direkt in der Filmbearbeitung hochladen, um TMDb-Daten zu korrigieren oder zu ergänzen.
- **Improved Backdrop Logic**: Behebung von Problemen bei der Backdrop-Anzeige (z.B. Alien-Saga), indem manuelle Uploads die automatische Fallback-Logik priorisiert überschreiben.
- **Refined Search UI**: Die Suchleisten auf dem Dashboard (Streaming & Classic) sowie in der Administration wurden deutlich kompakter und eleganter gestaltet.
- **Compact TMDb Modal**: Das Suchfenster für TMDb-Importe wurde verkleinert, um den Workflow bei der Filmbearbeitung zu verbessern.
- **UI Polishing**: Reduzierte Abstände und optimierte Schriftgrößen im gesamten Navigations- und Suchbereich für einen hochwertigeren Look.

## 🚀 Version v2.3.3 (Historie)

- **Immersive Cinematic Detail View**: Umstellung auf eine vollflächige, kinoreife Detailansicht (`movies/ID`) anstelle von AJAX-Panels für ein beeindruckendes Benutzererlebnis.
- **Interaktiver Serien-Support**: Neues, ausklappbares Accordion-System für Staffeln und Episoden inklusive Inhaltsangaben pro Folge.
- **Erweiterte Boxset-Logik**: Dedizierte Sektion für zusammengehörige Filme einer Kollektion innerhalb der Detailansicht.
- **Premium Cast-Ribbon**: Die Besetzung wird nun als elegantes Ribbon mit kreisförmigen Profilbildern und sanften Hover-Effekten präsentiert.
- **Echtzeit Watched-Toggle**: Direktes Markieren von Filmen als "Gesehen" mit sofortigem visuellem Feedback (Blue-Glow) via AJAX.
- **Navigation Unification**: Konsistente Link-Struktur im gesamten System (Dashboard, Schauspieler, Suche) für eine stabile Browser-Historie.
- **Sleek Pill-Button Design**: Modernisierte Pill-Shaped Action-Buttons (Trailer & Auswahl) im High-End-Streaming-Look.

## 🚀 Version v2.3.2 (Historie)
ion**: Schutz vor Cross-Site-Request-Forgery
- **Prepared Statements**: SQL-Injection-Schutz
- **Password Hashing**: Bcrypt-Verschlüsselung

## 🚀 Letzte Änderungen (v2.3.2)

- **Besetzungs-Slidebar (Cast Slider)**: Die Schauspielerliste auf der Detailseite ist nun in beiden Layouts ein eleganter, horizontaler Slider mit Snap-Effekt.
- **Profil-Streaming Integration**: Das Benutzerprofil wurde vollständig an das Streaming-Layout angepasst (Dark-Mode, Glassmorphismus, hybride Komponenten).
- **Intelligente Header-Logik**: Automatischer Ausgleich des Abstands für fixierte Header im Streaming-Modus, um überlappende Inhalte zu vermeiden.
- **500er & Dark-Mode Fixes**: Behebung von Serverfehlern bei der Layout-Umschaltung und Optimierung des Textkontrasts in Formularen.

## 🚀 Version v2.3.1 (Historie)

- **Dynamischer Hero-Slider**: Der Slider im Streaming-Layout wechselt nun automatisch alle 8 Sekunden zwischen den Top-Filmen der Sammlung.
- **Synchronisierte Paginierung**: Die Anzahl der Filme pro Ladung (Infinite Scroll) folgt nun strikt der Admin-Einstellung für "Filme pro Seite".
- **Actor Metadata Fix**: Vollständige Korrektur der Porträtbilder und Namen in der Besetzungsliste und den Detail-Ansichten (Umstellung auf `profile_url`).
- **UI-Cleanup**: Entfernung von Platzhalter-Buttons ("My List") für ein noch cleanereres Streaming-Erlebnis.

## 🚀 Version v2.3.0 (Historie)

- **Premium Streaming-Layout**: Einführung eines komplett neuen, immersiven User-Interfaces im "Cloud-Streaming"-Stil (Netflix/Disney+ Look).
- **Infinite Scrolling**: Nahtloses Nachladen von Filmen ("Lade mehr") für ein flüssiges Durchstöbern großer Sammlungen.
- **Glassmorphismus-Evolution**: Tiefere Blur-Effekte, moderne Navigations-Dropdowns und ein radikal bereinigter Footer für maximale Immersion.
- **Performance-Boost**: Optimierte AJAX-Schnittstellen und reduzierter DOM-Overhead im Dashboard.

## 🚀 Version v2.2.0 (Historie)

- **Actor Bot Daemon**: Neuer, leistungsstarker Hintergrundprozess im Adminpanel, der die Schauspieler-Datenbank autonom durchsucht und fehlende Biografie- sowie Profilbild-Daten automatisch via TMDb-API nachträgt.
- **Bereinigungs-Logik für Schauspieler**: Schauspieler ohne zugeordnete Filme ("Karteileichen werden durch den neuen Bot nun automatisch erkannt und sauber aus der Datenbank entfernt.
- **Bot Verlaufs-Dashboard**: Neues User Interface im Admin-Bereich zur Überwachung aktueller Bot-Läufe inklusive detaillierter Protokolle ("Logs") zu jedem verarbeiteten Schauspieler.

## 🚀 Version v2.1.7 (Historie)

- **Quill Rich-Text Editor**: Vollständige Integration eines WYSIWYG-Editors für Filmbeschreibungen (inkl. Dark-Mode Styling).
- **Actor Shortcodes**: Einführung von `{!Actor}Name}` Shortcodes zur automatischen Verlinkung von Schauspieler-Profilen in Beschreibungen.
- **Shortcode Toolbar**: Neuer Button im Editor zum einfachen Einfügen von Schauspielern per Klick.
- **Robustes Parsing**: Optimierter `ShortcodeService` mit Schutz gegen Verschachtelungsfehler und Unterstützung für verschiedene Klammertypen.
- **API-Profil-Management**: Neuer Endpunkt `PUT /api/user` zur Aktualisierung von Name, E-Mail und Passwort.
- **Sicherer 2FA-Login (API)**: Implementierung eines 2-Schritt-Login-Flows für die API mit `POST /api/login/2fa`.
- **Swagger UI Integration**: Interaktive API-Dokumentation unter `/api/documentation` mit vollständiger Sanctum/Bearer-Unterstützung.
- **UI-Fix Detailansicht**: Korrekte Darstellung von Absätzen und Listen in der Filmbeschreibung durch optimierte CSS-Regeln.

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


**Version:** 2.6.1  - Laravel Framework Edition

**Letztes Update**: 3. April 2026  
**Status**: Aktiv entwickelt

*Verwalten Sie Ihre Filmsammlung mit Stil und Effizienz!* 🎬✨
