![Logo](https://rene-neuhaus.eu/assets/logo/logo.png =200x200)

# MovieShelf - Dein digitales Filmregal 

Ein modernes, webbasiertes Tool zur Verwaltung Ihrer privaten Filmsammlung mit eleganter Benutzeroberfläche und umfangreichen Funktionen.

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

### 🎭 Schauspieler-Profile (NEU in v1.5.0)
- **Detaillierte Schauspieler-Profile** mit Biografien und Fotos
- **Filmografie-Übersicht** für jeden Schauspieler
- **Verknüpfung Film ↔ Schauspieler** mit Rolleninformationen
- **Inline-Editing** für schnelle Aktualisierungen

###  Benutzer-Features (NEU in v1.5.0)
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
- PHP 8.0+
- MySQL/MariaDB
- Webserver (Apache/Nginx)
- Modern Browser mit JavaScript-Unterstützung

### Verwendete Technologien
- **Backend**: PHP mit PDO
- **Frontend**: HTML5, CSS3 (Glass-Morphism), JavaScript
- **UI-Bibliotheken**: 
  - Bootstrap Icons
  - Fancybox für Lightbox-Funktionen
  - Chart.js für Statistiken
- **Datenbank**: MySQL/MariaDB (15 Tabellen)
- **APIs**: TMDb API für Film-Metadaten

## 📁 Projektstruktur

```
movieshelf/
├── admin/                  # Admin-Panel und Verwaltung
├── css/                    # Stylesheets und Themes
│   ├── style.css          # Haupt-Stylesheet
│   └── themes/            # Theme-Varianten
├── js/                     # JavaScript-Dateien
│   └── main.js            # Haupt-JavaScript
├── libs/                   # Externe Bibliotheken
│   └── fancybox/          # Fancybox Library
├── partials/              # Template-Teile
│   ├── header.php         # Header-Template
│   ├── film-list.php      # Film-Listen-Template
│   ├── actor-profile.php  # Schauspieler-Profile (NEU)
│   ├── impressum.php      # Impressum
│   └── datenschutz.php    # Datenschutzerklärung
├── includes/              # Core-Funktionen
│   ├── bootstrap.php      # System-Initialisierung
│   ├── functions.php      # Helper-Funktionen
│   └── version.php        # Versionsverwaltung
├── install/               # Installationsskripte
│   ├── index.php          # Installations-Wizard
│   └── sqldump/           # Datenbank-Schema
├── index.php              # Hauptdatei
└── README.md              # Diese Datei
```

## 🚀 Installation

### 1. Repository klonen
```bash
git clone https://github.com/lunasans/MovieShelf.git  
cd MovieShelf
```

### 2. Datenbank einrichten
- Erstellen Sie eine MySQL/MariaDB-Datenbank
- Importieren Sie das mitgelieferte SQL-Schema
- Konfigurieren Sie die Datenbankverbindung

### 3. Installation durchführen
- Öffnen Sie `http://ihre-domain.de/install/` im Browser
- Folgen Sie dem Installations-Wizard
- Erstellen Sie einen Admin-Benutzer
- Das System erstellt automatisch alle 15 Datenbank-Tabellen

### 4. XML-Import (Optional)
- Exportieren Sie Ihre Sammlung aus DVD Profiler als collection.xml
- Nutzen Sie die Import-Funktion im Admin-Panel

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
- **Datenbank-Wartungstools**
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


**Version**: 1.5.0 - Schauspieler-Profile Edition  
**Letztes Update**: Februar 2026  
**Status**: Aktiv entwickelt


