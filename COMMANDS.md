# 🛠️ MovieShelf Kommando-Referenz

Diese Dokumentation enthält alle wichtigen Befehle zur Verwaltung und Wartung von MovieShelf.

## 🚀 Standard-Entwicklung & Deployment

### Lokalen Server starten
Startet den eingebauten PHP-Server für die Entwicklung.
```bash
php artisan serve
```

### Assets kompilieren (Vite/Tailwind)
Erstellt die fertigen CSS- und JS-Dateien für den Browser.
```bash
# Während der Entwicklung (Auto-Reload)
npm run dev

# Für den Live-Betrieb (Kompilieren)
npm run build
```

### Datenbank aktualisieren
Wendet neue Strukturänderungen an.
```bash
php artisan migrate --force
```

---

## 🎬 Custom Artisan Befehle (MovieShelf Spezial)

### Schauspieler-Duplikate bereinigen
Sucht nach doppelten Schauspielern (gleicher Vor- & Nachname) und führt diese zu einem sauberen Datensatz zusammen.
```bash
php artisan app:merge-duplicate-actors
```

### Film-Duplikate finden & bereinigen
Sucht nach Filmen mit gleichem Titel und gleichem Sammlungstyp.
```bash
# Nur anzeigen
php artisan app:find-duplicate-movies

# Automatisch zusammenführen (behält den Datensatz mit den meisten Infos)
php artisan app:find-duplicate-movies --merge
```

### Benutzerverwaltung
Erstellt einen neuen Admin-Benutzer oder aktualisiert Passwörter.
```bash
# Benutzer erstellen
php artisan app:create-user

# Passwort ändern
php artisan app:update-password
```

### V1 Datenmigration
Importiert Daten aus einer MovieShelf v1.5 Datenbank.
```bash
php artisan app:migrate-v1
```

---

## 🧹 Wartung & Problembehebung

### Cache leeren
Hilft oft, wenn Änderungen am Code oder an der Konfiguration nicht sofort sichtbar sind.
```bash
php artisan optimize:clear
```

### System-Logs einsehen
```bash
# Aktuelle Logs anzeigen (Windows PowerShell)
Get-Content storage/logs/laravel.log -Tail 50 -Wait

# Aktuelle Logs anzeigen (Linux/Mac)
tail -f storage/logs/laravel.log
```

---

## 🔄 System-Update

### Automatisches Update (Empfohlen)
MovieShelf verfügt über eine integrierte Update-Funktion im Admin-Panel (**Administration -> System aktualisieren**).

### Manuelles Update (Terminal)
Falls das automatische Update nicht möglich ist, können folgende Befehle nacheinander ausgeführt werden:

```bash
# 1. Neuesten Code abrufen
git pull

# 2. Datenbank-Migrationen ausführen
php artisan migrate --force

# 3. Cache leeren (wichtig für Versionsanzeige & neue Routen)
php artisan optimize:clear

# 4. Abhängigkeiten aktualisieren & Frontend bauen
npm install
npm run build
```

---

## 🐳 Docker Deployment

MovieShelf unterstützt Docker für einfaches Deployment und lokale Entwicklung.

### Container starten
Baut das Image und startet alle Dienste im Hintergrund.
```bash
docker-compose up -d --build
```

### Befehle im Container ausführen
Verwenden Sie `docker-compose exec app`, um Artisan- oder Composer-Befehle auszuführen.

**Beispiel: Migrationen ausführen**
```bash
docker-compose exec app php artisan migrate --force
```

**Beispiel: Cache leeren**
```bash
docker-compose exec app php artisan optimize:clear
```

### Logs einsehen
```bash
docker-compose logs -f app
```

### Dienste stoppen
```bash
docker-compose down
```
