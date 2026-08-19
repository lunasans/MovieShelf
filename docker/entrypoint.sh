#!/bin/sh
#
# Container-Start: Umgebung herrichten, dann an supervisord uebergeben.
# Alles hier ist idempotent - der Container darf beliebig oft neu starten.
#
set -e

APP_DIR=/var/www/html
cd "$APP_DIR"

log() { echo "[entrypoint] $*"; }

as_www() { su -s /bin/sh -c "$*" www-data; }

# --------------------------------------------------------------- APP_KEY ---
# Der Container arbeitet ohne .env-Datei - alle Einstellungen kommen als
# Umgebungsvariablen aus dem compose-File. Nur der APP_KEY muss erhalten
# bleiben: ohne ihn waeren nach jedem Neustart alle Sessions und alle
# verschluesselten Werte (u.a. die 2FA-Secrets) unbrauchbar. Er landet daher
# im persistenten storage-Volume.
KEY_FILE="$APP_DIR/storage/app/app_key"

if [ -z "${APP_KEY:-}" ]; then
    if [ ! -s "$KEY_FILE" ]; then
        log "Kein APP_KEY gesetzt - generiere einen und lege ihn in $KEY_FILE ab"
        mkdir -p "$(dirname "$KEY_FILE")"
        php -r 'echo "base64:".base64_encode(random_bytes(32));' > "$KEY_FILE"
        chown www-data:www-data "$KEY_FILE"
        chmod 0600 "$KEY_FILE"
    fi
    APP_KEY="$(cat "$KEY_FILE")"
    export APP_KEY
    log "APP_KEY aus $KEY_FILE geladen"
fi

# --------------------------------------------------------------- SQLite ---
# Beim Default DB_CONNECTION=sqlite muss die Datei existieren, bevor migriert
# werden kann. Sie liegt in database/ und damit im Volume.
DB_CONN="${DB_CONNECTION:-sqlite}"
if [ "$DB_CONN" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-$APP_DIR/database/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        log "Lege SQLite-Datenbank an: $DB_FILE"
        install -o www-data -g www-data -m 0664 /dev/null "$DB_FILE"
    fi
fi

# ------------------------------------------------------- Verzeichnisse ---
# Gemountete Volumes kommen je nach Host mit fremdem Owner an.
log "Setze Rechte auf storage/, bootstrap/cache und database/"
mkdir -p \
    storage/app/public \
    storage/app/backups \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache database

# --------------------------------------------------------- storage:link ---
# Muss zur Laufzeit passieren: im Build wuerde das Volume den Symlink verdecken.
if [ ! -e "$APP_DIR/public/storage" ]; then
    log "Erzeuge public/storage -> storage/app/public"
    as_www "php artisan storage:link --no-interaction"
fi

# ------------------------------------------------------------ Warten ---
# Bei der MariaDB-Variante ist der DB-Container evtl. noch nicht bereit.
if [ "$DB_CONN" = "mysql" ] || [ "$DB_CONN" = "mariadb" ]; then
    log "Warte auf Datenbank ${DB_HOST:-db}:${DB_PORT:-3306}"
    i=0
    until as_www "php artisan db:show" >/dev/null 2>&1; do
        i=$((i + 1))
        if [ "$i" -ge 60 ]; then
            log "Datenbank nach 60 Versuchen nicht erreichbar - breche ab"
            exit 1
        fi
        sleep 2
    done
    log "Datenbank erreichbar"
fi

# --------------------------------------------------------- Migrationen ---
# Das ersetzt fuer Docker-Installationen das update.sh: neues Image ziehen,
# Container neu starten, Schema ist aktuell.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    log "Fuehre Migrationen aus"
    as_www "php artisan migrate --force --no-interaction"
else
    log "RUN_MIGRATIONS=false - ueberspringe Migrationen"
fi

# ------------------------------------------------------ Paket-Erkennung ---
# Im Build uebersprungen (dort fehlt pdo_mysql fuer config/database.php),
# deshalb hier nachholen - sonst fehlen die Service-Provider der Pakete.
log "Ermittle Paket-Provider"
as_www "php artisan package:discover --no-interaction" >/dev/null

# --------------------------------------------------------------- Caches ---
# Erst hier cachen, nicht im Build: die Config haengt an den Laufzeit-Env-Werten.
log "Baue Config-, Route- und View-Cache"
as_www "php artisan optimize:clear" >/dev/null
as_www "php artisan config:cache"
as_www "php artisan route:cache"
as_www "php artisan view:cache"

log "Bereit - starte Dienste"
exec "$@"
