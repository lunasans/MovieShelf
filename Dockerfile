# syntax=docker/dockerfile:1.7

# =============================================================================
# MovieShelf - Selfhosted Image (nginx + php-fpm + supervisor in einem Container)
# =============================================================================
# Build:  docker build -t movieshelf .
# Start:  docker compose up -d
# =============================================================================

ARG PHP_VERSION=8.3
ARG NODE_VERSION=24

# --------------------------------------------------------------- Stage 1 ---
# PHP-Abhaengigkeiten. Eigener Stage, damit Composer nicht ins Runtime-Image muss.
FROM php:${PHP_VERSION}-cli-alpine AS vendor

RUN apk add --no-cache git unzip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Erst nur die Manifeste kopieren: solange die sich nicht aendern, bleibt der
# teure composer-install-Layer im Cache.
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

# Jetzt der Rest des Codes, danach der Autoloader (braucht die Klassen).
COPY . .
# --no-scripts: package:discover wuerde Laravel booten, und config/database.php
# braucht dafuer die pdo_mysql-Konstanten. Dieser Stage legt aber nur Dateien
# zusammen und hat die Extension nicht - die Erkennung passiert im Entrypoint.
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts

# --------------------------------------------------------------- Stage 2 ---
# Frontend-Assets. Node landet nicht im Runtime-Image.
FROM node:${NODE_VERSION}-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm npm ci

COPY vite.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# --------------------------------------------------------------- Stage 3 ---
# Runtime.
FROM php:${PHP_VERSION}-fpm-alpine AS runtime

ARG APP_VERSION=dev
# RUN_QUEUE_WORKER und RUN_SCHEDULER liest supervisor als autostart-Wert.
# Auf "false" setzen, wenn Queue oder Scheduler in eigenen Containern laufen.
ENV APP_VERSION=${APP_VERSION} \
    TZ=UTC \
    RUN_QUEUE_WORKER=true \
    RUN_SCHEDULER=true

# Laufzeit-Pakete. nginx und supervisor bedienen den Container,
# der Rest sind Bibliotheken fuer die PHP-Extensions.
#
# apk upgrade zuerst: das Basis-Image wird nur zu seinen eigenen Releases neu
# gebaut, Alpine veroeffentlicht Sicherheitspatches aber laufend. Ohne das
# schleppt jedes Image die Luecken seines Basis-Stands mit - u.a. nghttp2 aus
# curl und py3-setuptools, das supervisor als Python-Programm mitbringt.
RUN apk upgrade --no-cache \
    && apk add --no-cache \
        nginx \
        supervisor \
        tzdata \
        curl \
        icu-libs \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        libgomp \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        intl \
        zip \
        bcmath \
        exif \
        pcntl \
        pdo_mysql \
        opcache \
    && apk del .build-deps

# pdo_sqlite und sqlite3 sind im Basis-Image bereits aktiv - SQLite ist der
# Default dieser App und braucht daher keine zusaetzliche Extension.

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-movieshelf.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-movieshelf.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

# Applikation zusammensetzen: Code + Vendor aus Stage 1, gebaute Assets aus Stage 2.
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# Verzeichnisse, die zur Laufzeit beschreibbar sein muessen. Die .env-Datei wird
# vom Entrypoint angelegt, wenn keine gemountet ist.
RUN rm -f .env \
    && mkdir -p \
        storage/app/public \
        storage/app/backups \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        /var/lib/nginx/tmp \
        /var/log/supervisor \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chown -R www-data:www-data /var/lib/nginx /var/log/nginx

# Persistente Daten: Datenbank (SQLite), Uploads/Cover und Logs.
VOLUME ["/var/www/html/storage", "/var/www/html/database"]

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
