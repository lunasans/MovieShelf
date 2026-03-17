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
