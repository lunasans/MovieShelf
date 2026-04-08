# MovieShelf 2.13.3
Width & Cache Fix

## Changelog 2.13.3
- **Fix**: Maximale Breite des Screenshot-Sliders auf **800px** reduziert für ein kompakteres Erscheinungsbild.
- **Fix**: CSS-Regeln mit `!important` verstärkt, um Cache-Probleme oder Overrides zu umgehen.
- **Cache**: Laravel View-Cache manuell bereinigt.

---

## Changelog 2.13.2 (Archiv)
- **Fix**: Screenshot-Slider Größe auf das exakte Originalmaß (Container-Breite) zurückgesetzt.
- **Fix**: CSS-Limitierung auf 1000px entfernt, da dies als "zu groß" bzw. unpassend wahrgenommen wurde.
- **Fix**: `fade-up` Animation vom Slider entfernt für einen direkteren Look.

---

## Changelog 2.13.1 (Archiv)
- **Refinement**: Maximale Breite des Screenshot-Sliders auf 1000px begrenzt für bessere Optik.
- **Refinement**: Auto-Play Intervall auf 10 Sekunden verlängert.

---

## Changelog 2.13.0 (Archiv)
- **Neu**: Interaktiver Screenshot-Slider auf der Landingpage mit Alpine.js.
- **Neu**: Automatische Rotation und manuelle Navigation für Projekt-Features.
- **Fix**: `RouteNotFoundException` behoben, die beim Bestätigen der Regal-Löschung auftrat.
- **Fix**: Lösch-Links werden nun immer für die zentrale Domain generiert.
- **Fix**: Nach der Löschung erfolgt ein absoluter Redirect auf die zentrale Startseite, um Kontext-Konflikte zu vermeiden.

---

## Changelog 2.12.3 (Archiv)
- **Fix**: Doppel-Initialisierung des Quill-Editors in Admin-Panel behoben.
- **Fix**: Zu große Zeilenabstände (Margins) auf der Impressum-Seite korrigiert.
- **Fix**: Layout-Problem in den Plattform-Einstellungen (E-Mail-Sektion) behoben.
- **SaaS**: Zentrales FAQ-System im Global ACP mit Quill-Integration für die Landingpage.
- **SaaS**: Verwaltbares Impressum direkt über das Global ACP (aktivierbar/deaktivierbar).
- **Security**: Subdomain-Blocklist (einstellbar) zur Verhinderung reservierter Namen.
- **Compliance**: Pflicht-Checkbox für Nutzungsbedingungen im Registrierungsprozess.

---

## Changelog 2.10.0 (Archiv)
- **Neu**: Komplette Registrierung (Name, E-Mail, Passwort) direkt auf der Landingpage.
- **Neu**: Automatisierte Bestätigungsmail (`TenantWelcome`) nach erfolgreicher Einrichtung.
- **Fix**: Dynamische URL-Erkennung (kein Hardcoding von `localhost:8000` mehr).
- **Fix**: Strukturierte Datenbank-Isolation (SQLite) für SaaS-Nutzer.
- **SaaS**: Optimierter Onboarding-Prozess für isolierte Filmregal-Instanzen.

---

# MovieShelf
Dein persönliches, digitales Filmregal.

## Installation
...
