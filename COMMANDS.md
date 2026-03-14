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

## 🔄 Automatisches System-Update
MovieShelf verfügt über eine integrierte Update-Funktion im Admin-Panel (**Administration -> System aktualisieren**).

**Dieser Prozess automatisiert folgende Schritte:**
1. `git stash` (Sichert lokale Code-Änderungen)
2. `git pull` (Lädt den neuesten Stand von GitHub)
3. `git stash pop` (Spielt lokale Änderungen zurück)
4. `php artisan migrate --force` (Stellt DB-Struktur sicher)
5. `npm install` (Aktualisiert Bibliotheken)
6. `npm run build` (Baut das Frontend neu)
