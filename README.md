# MovieShelf 2.12.1
Registrierungs-Patch & Stabilitäts-Update

## Changelog 2.12.1
- **Fix**: Registrierung robuster gestaltet (Fehlende Felder 'username' und 'password_confirmation' werden nun automatisch gehandhabt).
- **Fix**: Mail-Versand bei der Registrierung abgesichert (try-catch), damit Fehlkonfigurationen des Mail-Servers nicht den gesamten Prozess blockieren.

---

## Changelog 2.12.0 (Archiv)
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
