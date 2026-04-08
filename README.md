# MovieShelf 2.13.0
Screenshot Slider Feature

## Changelog 2.13.0
- **Neu**: Interaktiver Screenshot-Slider auf der Landingpage mit Alpine.js.
- **Neu**: Automatische Rotation und manuelle Navigation für Projekt-Features.

---

## Changelog 2.12.4 (Archiv)
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
