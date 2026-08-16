#!/usr/bin/env bash
#
# MovieShelf SaaS – Interaktives Update-Script
# ---------------------------------------------
# Wird DIREKT auf dem Server im App-Verzeichnis ausgefuehrt:
#     cd /home/<user>/htdocs/<domain>/MovieShelf
#     ./update.sh
#
# Jeder Schritt wird einzeln abgefragt (j/N). Nichts passiert ohne Bestaetigung.
#
set -uo pipefail

# ------------------------------------------------------------------ Farben ---
if [ -t 1 ]; then
    C_RESET=$'\e[0m'; C_DIM=$'\e[2m'; C_BOLD=$'\e[1m'
    C_RED=$'\e[31m'; C_GREEN=$'\e[32m'; C_YELLOW=$'\e[33m'
    C_BLUE=$'\e[34m'; C_CYAN=$'\e[36m'
else
    C_RESET=""; C_DIM=""; C_BOLD=""; C_RED=""; C_GREEN=""; C_YELLOW=""; C_BLUE=""; C_CYAN=""
fi

# --------------------------------------------------------------- Verzeichnis ---
# App-Verzeichnis = Ordner, in dem dieses Script liegt.
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR" || { echo "Kann nicht ins App-Verzeichnis wechseln."; exit 1; }

START_TS=$(date +%s)
BACKUP_DIR="$APP_DIR/storage/app/backups"
LOG_FILE="$APP_DIR/storage/logs/update-$(date +%Y%m%d-%H%M%S).log"
BRANCH="${UPDATE_BRANCH:-main}"     # Ziel-Branch, override: UPDATE_BRANCH=xyz ./update.sh
ROLLBACK_COMMIT=""
MAINT_ON=0

# ------------------------------------------------------------- Hilfsfunktionen ---
log()   { echo "$*" | tee -a "$LOG_FILE"; }
head_() { echo ""; log "${C_BOLD}${C_BLUE}==> $*${C_RESET}"; }
ok()    { log "${C_GREEN}  ✓ $*${C_RESET}"; }
warn()  { log "${C_YELLOW}  ! $*${C_RESET}"; }
err()   { log "${C_RED}  ✗ $*${C_RESET}"; }

# Ja/Nein-Abfrage. Rueckgabe 0 = ja. Default = Nein.
confirm() {
    local prompt="$1" ans
    printf "%s%s [j/N] %s" "${C_CYAN}" "$prompt" "${C_RESET}"
    read -r ans < /dev/tty
    [[ "$ans" =~ ^([jJ]|[yY])$ ]]
}

# Fuehrt einen Befehl aus, loggt ihn, bricht bei Fehler das Script ab.
run() {
    log "${C_DIM}\$ $*${C_RESET}"
    if "$@" >>"$LOG_FILE" 2>&1; then
        return 0
    else
        local rc=$?
        err "Befehl fehlgeschlagen (Exit $rc): $*"
        err "Details siehe Log: $LOG_FILE"
        abort_recover
        exit $rc
    fi
}

# Liest einen Wert aus der .env (ohne umgebende Anfuehrungszeichen).
env_get() {
    local key="$1"
    grep -E "^${key}=" "$APP_DIR/.env" 2>/dev/null | head -1 | cut -d= -f2- \
        | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"
}

# Bei Abbruch/Fehler: Wartungsmodus wieder deaktivieren + Rollback-Hinweis.
abort_recover() {
    if [ "$MAINT_ON" -eq 1 ]; then
        warn "Deaktiviere Wartungsmodus wieder ..."
        $PHP artisan up >>"$LOG_FILE" 2>&1 && MAINT_ON=0
    fi
    if [ -n "$ROLLBACK_COMMIT" ]; then
        echo ""
        warn "ROLLBACK moeglich – auf den Stand vor dem Update zuruecksetzen mit:"
        echo "    git reset --hard $ROLLBACK_COMMIT"
        echo "    $PHP artisan migrate:rollback   # nur falls Migrationen liefen"
    fi
}

# Ctrl+C sauber abfangen.
trap 'echo ""; err "Abgebrochen (SIGINT)."; abort_recover; exit 130' INT

# --------------------------------------------------------------- PHP-Binary ---
detect_php() {
    if [ -n "${PHP_BIN:-}" ] && command -v "$PHP_BIN" >/dev/null 2>&1; then
        PHP="$PHP_BIN"; return
    fi
    local c
    for c in php php8.5 php8.4 php8.3 php8.2 \
             /usr/bin/php8.5 /usr/bin/php8.4 /usr/bin/php8.3 /usr/bin/php8.2; do
        if command -v "$c" >/dev/null 2>&1; then PHP="$c"; return; fi
    done
    PHP=""
}

# ===================================================================== START ===
mkdir -p "$BACKUP_DIR" "$(dirname "$LOG_FILE")"

clear
log "${C_BOLD}${C_CYAN}"
log "  ╔══════════════════════════════════════════════╗"
log "  ║        MovieShelf SaaS – Server-Update        ║"
log "  ╚══════════════════════════════════════════════╝"
log "${C_RESET}"

# ---- Preflight ---------------------------------------------------------------
head_ "Vorpruefung"

[ -f "$APP_DIR/artisan" ] || { err "Keine artisan-Datei in $APP_DIR – falsches Verzeichnis?"; exit 1; }
ok "App-Verzeichnis: $APP_DIR"

git rev-parse --is-inside-work-tree >/dev/null 2>&1 || { err "Kein Git-Repository."; exit 1; }

detect_php
[ -n "$PHP" ] || { err "Kein PHP gefunden. Setze PHP_BIN, z.B.: PHP_BIN=/usr/bin/php8.3 ./update.sh"; exit 1; }
ok "PHP:      $($PHP -v | head -1)"
ok "Composer: $(command -v composer >/dev/null 2>&1 && composer --version 2>/dev/null | head -1 || echo 'NICHT gefunden')"
ok "Node/npm: $(command -v npm >/dev/null 2>&1 && echo "npm $(npm -v)" || echo 'NICHT gefunden')"

command -v composer >/dev/null 2>&1 || warn "composer fehlt – Composer-Schritt wird spaeter fehlschlagen."
command -v npm      >/dev/null 2>&1 || warn "npm fehlt – Frontend-Build-Schritt wird spaeter fehlschlagen."

CUR_COMMIT=$(git rev-parse --short HEAD)
CUR_VERSION=$($PHP artisan tinker --execute="echo config('app.saas_version');" 2>/dev/null | tail -1)
log ""
log "  Aktueller Stand : ${C_BOLD}$CUR_COMMIT${C_RESET}  (SaaS-Version ${CUR_VERSION:-?})"
log "  Ziel-Branch     : ${C_BOLD}$BRANCH${C_RESET}"
log "  Log-Datei       : $LOG_FILE"

# Working tree sauber?
if [ -n "$(git status --porcelain)" ]; then
    warn "Es gibt lokale, nicht committete Aenderungen im Arbeitsverzeichnis:"
    git status --short | tee -a "$LOG_FILE"
    echo ""
    if ! confirm "Trotzdem fortfahren? (lokale Aenderungen koennten den git pull blockieren)"; then
        err "Abgebrochen."; exit 1
    fi
fi

# Eingehende Commits anzeigen
head_ "Verfuegbare Aenderungen abrufen (git fetch)"
run git fetch --prune origin
INCOMING=$(git log --oneline "HEAD..origin/$BRANCH" 2>/dev/null)
if [ -z "$INCOMING" ]; then
    ok "Bereits auf dem neuesten Stand von origin/$BRANCH."
    if ! confirm "Trotzdem den restlichen Update-Prozess (Build/Caches) durchlaufen?"; then
        log "Nichts zu tun. Ende."; exit 0
    fi
else
    log "  Neue Commits auf origin/$BRANCH:"
    echo "$INCOMING" | sed 's/^/    /' | tee -a "$LOG_FILE"
fi

echo ""
if ! confirm "${C_BOLD}Update jetzt starten?${C_RESET}"; then
    log "Abgebrochen. Es wurde nichts veraendert."; exit 0
fi

# ---- 1) Backup ---------------------------------------------------------------
head_ "1/7  Backup"
if confirm "Datenbank-Backup + Stand sichern?"; then
    ROLLBACK_COMMIT="$CUR_COMMIT"
    echo "$CUR_COMMIT" > "$BACKUP_DIR/last-commit-$(date +%Y%m%d-%H%M%S).txt"
    ok "Aktueller Commit fuer Rollback notiert: $CUR_COMMIT"

    DB_CONN=$(env_get DB_CONNECTION)
    DB_DATABASE=$(env_get DB_DATABASE)
    if [[ "$DB_CONN" == *sqlite* ]] || [[ "$DB_DATABASE" == *.sqlite ]]; then
        if [ -f "$DB_DATABASE" ]; then
            run cp "$DB_DATABASE" "$BACKUP_DIR/db-$(date +%Y%m%d-%H%M%S).sqlite"
            ok "SQLite-Datenbank gesichert."
        else
            warn "SQLite-Pfad nicht gefunden ($DB_DATABASE) – DB-Backup uebersprungen."
        fi
    else
        if command -v mysqldump >/dev/null 2>&1; then
            local_dump="$BACKUP_DIR/db-$(date +%Y%m%d-%H%M%S).sql"
            DB_HOST=$(env_get DB_HOST); DB_PORT=$(env_get DB_PORT)
            DB_USER=$(env_get DB_USERNAME); DB_PASS=$(env_get DB_PASSWORD)
            log "${C_DIM}\$ mysqldump ${DB_DATABASE} > $local_dump${C_RESET}"
            if MYSQL_PWD="$DB_PASS" mysqldump \
                    -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" \
                    -u "$DB_USER" --single-transaction --quick --skip-lock-tables \
                    "$DB_DATABASE" > "$local_dump" 2>>"$LOG_FILE"; then
                gzip -f "$local_dump" 2>/dev/null || true
                ok "Zentrale Datenbank gesichert: ${local_dump}.gz"
                warn "Hinweis: Tenant-Datenbanken werden hier NICHT mitgesichert."
            else
                err "mysqldump fehlgeschlagen (Details im Log)."
                confirm "Ohne DB-Backup fortfahren?" || { err "Abgebrochen."; exit 1; }
            fi
        else
            warn "mysqldump nicht verfuegbar – DB-Backup uebersprungen."
            confirm "Ohne DB-Backup fortfahren?" || { err "Abgebrochen."; exit 1; }
        fi
    fi
else
    warn "Backup uebersprungen."
fi

# ---- 2) Wartungsmodus --------------------------------------------------------
head_ "2/7  Wartungsmodus"
if confirm "Seite in den Wartungsmodus schalten (Nutzer sehen Wartungsseite)?"; then
    run $PHP artisan down --retry=15
    MAINT_ON=1
    ok "Wartungsmodus aktiv."
else
    warn "Wartungsmodus uebersprungen – Update laeuft im laufenden Betrieb."
fi

# ---- 3) Code aktualisieren ---------------------------------------------------
head_ "3/7  Code aktualisieren (git pull)"
if confirm "git pull origin $BRANCH ausfuehren?"; then
    run git pull --ff-only origin "$BRANCH"
    NEW_COMMIT=$(git rev-parse --short HEAD)
    ok "Jetzt auf Commit: $NEW_COMMIT"
else
    warn "git pull uebersprungen."
fi

# ---- 4) PHP-Abhaengigkeiten --------------------------------------------------
head_ "4/7  Composer-Abhaengigkeiten"
if confirm "composer install --no-dev --optimize-autoloader ausfuehren?"; then
    run composer install --no-dev --optimize-autoloader --no-interaction
    ok "Composer-Abhaengigkeiten aktuell."
else
    warn "Composer-Schritt uebersprungen."
fi

# ---- 5) Frontend-Build -------------------------------------------------------
head_ "5/7  Frontend-Build (npm)"
if confirm "npm ci && npm run build ausfuehren?"; then
    if [ -f package-lock.json ]; then run npm ci; else run npm install; fi
    run npm run build
    ok "Assets neu gebaut (public/build)."
else
    warn "Frontend-Build uebersprungen."
fi

# ---- 6) Datenbank-Migration --------------------------------------------------
head_ "6/7  Datenbank-Migration"
warn "Migrationen veraendern die Datenbank-Struktur (auch aller Tenants)."
if confirm "php artisan migrate --force ausfuehren?"; then
    run $PHP artisan migrate --force
    ok "Zentrale Migrationen ausgefuehrt."
    if $PHP artisan list 2>/dev/null | grep -q "tenants:migrate"; then
        if confirm "Auch Tenant-Migrationen ausfuehren (tenants:migrate)?"; then
            run $PHP artisan tenants:migrate --force
            ok "Tenant-Migrationen ausgefuehrt."
        fi
    fi
else
    warn "Migration uebersprungen."
fi

# ---- 7) Caches & Abschluss ---------------------------------------------------
head_ "7/7  Caches neu aufbauen & Dienste"
if confirm "Config/Route/View-Caches neu aufbauen + queue:restart?"; then
    run $PHP artisan config:cache
    run $PHP artisan route:cache
    run $PHP artisan view:cache
    [ -L "$APP_DIR/public/storage" ] || $PHP artisan storage:link >>"$LOG_FILE" 2>&1 || true
    run $PHP artisan queue:restart
    ok "Caches aktualisiert, Queue-Worker signalisiert."
else
    warn "Cache-Schritt uebersprungen."
    warn "Achtung: Ohne config:clear/cache kann eine veraltete Config aktiv bleiben."
fi

# ---- Wartungsmodus aus -------------------------------------------------------
if [ "$MAINT_ON" -eq 1 ]; then
    head_ "Wartungsmodus deaktivieren"
    run $PHP artisan up
    MAINT_ON=0
    ok "Seite wieder online."
fi

# ---- Zusammenfassung ---------------------------------------------------------
FINAL_COMMIT=$(git rev-parse --short HEAD)
FINAL_VERSION=$($PHP artisan tinker --execute="echo config('app.saas_version');" 2>/dev/null | tail -1)
DURATION=$(( $(date +%s) - START_TS ))

echo ""
log "${C_GREEN}${C_BOLD}  ✓ Update abgeschlossen${C_RESET}"
log "  Commit : ${CUR_COMMIT}  →  ${C_BOLD}${FINAL_COMMIT}${C_RESET}"
log "  Version: SaaS ${FINAL_VERSION:-?}"
log "  Dauer  : ${DURATION}s"
log "  Log    : $LOG_FILE"
echo ""
