# MovieShelf

Self-hosted web application for cataloguing, browsing and presenting a personal
DVD and Blu-ray collection. Import from TMDb or DVD Profiler, keep track of what
you have watched, and share a polished view of your shelf.

Runs as a single container. No external services required.

---

## Quick start

Create a `docker-compose.yml`:

```yaml
services:
  app:
    image: tessaa/movieshelf:latest
    container_name: movieshelf
    restart: unless-stopped
    ports:
      - "8080:8080"
    environment:
      # Set this to the address you will actually reach the app on.
      # Links and outgoing mail are built from it.
      APP_URL: http://localhost:8080
      APP_NAME: MovieShelf
      APP_LOCALE: en
      TZ: Europe/Berlin
    volumes:
      - movieshelf-storage:/var/www/html/storage
      - movieshelf-database:/var/www/html/database

volumes:
  movieshelf-storage:
  movieshelf-database:
```

Then start it:

```bash
docker compose up -d
```

MovieShelf is now available at <http://localhost:8080>. The first account you
register becomes the administrator.

Nothing else needs preparing. On first start the container generates its
application key, creates the SQLite database, applies the migrations and starts
the web server, the queue worker and the scheduler.

---

## What runs inside the container

One image carries the whole application, supervised by `supervisord`:

| Process | Role |
| --- | --- |
| nginx | Serves the application on port 8080 |
| php-fpm | Runs the PHP application |
| Queue worker | Mail, TMDb imports, trailer lookups |
| Scheduler | Replaces the system cron for recurring jobs |

The queue worker and the scheduler can be switched off if you would rather run
them as separate containers. See `RUN_QUEUE_WORKER` and `RUN_SCHEDULER` below.

---

## Configuration

The container takes its settings from environment variables. It does not use an
`.env` file.

### Common

| Variable | Default | Description |
| --- | --- | --- |
| `APP_URL` | `http://localhost` | Public address of the instance. Set this, or links and mail will point at localhost. |
| `APP_NAME` | `MovieShelf` | Shown in the interface and in outgoing mail. |
| `APP_LOCALE` | `de` | Interface language. English and German are available. |
| `TZ` | `UTC` | Time zone for the scheduler and for displayed timestamps. |
| `APP_KEY` | generated | Created on first start and kept in the storage volume. Set it only if you want to manage it yourself. |

### Database

| Variable | Default | Description |
| --- | --- | --- |
| `DB_CONNECTION` | `sqlite` | Either `sqlite` or `mysql`. |
| `DB_HOST`, `DB_PORT` | | Required for `mysql`. |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | | Required for `mysql`. |

### Behaviour

| Variable | Default | Description |
| --- | --- | --- |
| `RUN_MIGRATIONS` | `true` | Apply pending migrations on start. |
| `RUN_QUEUE_WORKER` | `true` | Run the queue worker inside the container. |
| `RUN_SCHEDULER` | `true` | Run the scheduler inside the container. |
| `TRUSTED_PROXIES` | empty | IP or CIDR of your reverse proxy. Never set this to `*`, which would make `X-Forwarded-For` and every IP-based rate limit forgeable. |
| `LOG_LEVEL` | `debug` | Use `warning` in production. |

### Mail

Required for registration mail and for notifications about new episodes of
followed series.

```yaml
      MAIL_MAILER: smtp
      MAIL_HOST: smtp.example.com
      MAIL_PORT: "587"
      MAIL_USERNAME: ""
      MAIL_PASSWORD: ""
      MAIL_FROM_ADDRESS: movieshelf@example.com
```

---

## Data and backups

Two volumes hold everything that has to survive a restart:

| Volume | Path | Contents |
| --- | --- | --- |
| `movieshelf-storage` | `/var/www/html/storage` | Cover art, uploads, backups, logs, the generated application key |
| `movieshelf-database` | `/var/www/html/database` | The SQLite database |

Backing up both is a complete backup of the instance. With MariaDB, back up its
volume instead of `movieshelf-database`.

---

## Updating

Pull the new image and restart. Migrations are applied automatically on start.

```bash
docker compose pull
docker compose up -d
```

---

## Behind a reverse proxy

The container speaks plain HTTP on port 8080. TLS termination and security
headers belong to the proxy in front of it, such as Traefik, nginx or Caddy.

Set `APP_URL` to the public HTTPS address, and `TRUSTED_PROXIES` to the network
your proxy runs in:

```yaml
      APP_URL: https://movies.example.com
      TRUSTED_PROXIES: 172.16.0.0/12
```

---

## MariaDB instead of SQLite

SQLite is the default and is enough for a personal collection. MariaDB becomes
worthwhile once you have several thousand titles, or several people using the
instance at the same time, because SQLite serialises every write.

Add a second file, `docker-compose.mariadb.yml`:

```yaml
services:
  app:
    environment:
      DB_CONNECTION: mysql
      DB_HOST: db
      DB_PORT: "3306"
      DB_DATABASE: movieshelf
      DB_USERNAME: movieshelf
      DB_PASSWORD: change-me
    depends_on:
      db:
        condition: service_healthy

  db:
    image: mariadb:11
    container_name: movieshelf-db
    restart: unless-stopped
    environment:
      MARIADB_DATABASE: movieshelf
      MARIADB_USER: movieshelf
      MARIADB_PASSWORD: change-me
      MARIADB_RANDOM_ROOT_PASSWORD: "yes"
    volumes:
      - movieshelf-mariadb:/var/lib/mysql
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 10s
      timeout: 5s
      retries: 12
      start_period: 30s

volumes:
  movieshelf-mariadb:
```

Change both passwords before the first start, then:

```bash
docker compose -f docker-compose.yml -f docker-compose.mariadb.yml up -d
```

Switching between SQLite and MariaDB does not migrate any data. Export from the
admin panel first, then import again after the switch.

---

## Images and tags

Both registries receive the same image with the same digest.

```
tessaa/movieshelf:latest
ghcr.io/lunasans/movieshelf:latest
```

| Tag | Meaning |
| --- | --- |
| `latest` | The most recent release |
| `2.42.0` | One specific release |
| `2.42` | Latest patch of that minor release |

Images are published from releases only, so `latest` always points at a version
that was tagged deliberately rather than at whatever landed on the main branch
last.

Every image is built for `linux/amd64` and `linux/arm64`, which covers NAS
devices, the Raspberry Pi and Apple Silicon.

The current release is rebuilt and scanned weekly, so operating system security
updates reach you without waiting for the next version. Application code only
ever changes with a release.

---

## Features

**Importing**

- TMDb import for films and series, including cast and artwork
- XML import from DVD Profiler collections
- Box set detection with grouped, expandable entries
- Duplicate detection by TMDb ID, and by name for people

**Browsing and presentation**

- Grid and list views with cover art
- Full text search across all metadata, with filters for genre, year and rating
- Detail pages with cast, artwork and trailers
- Actor profiles with biography and filmography
- Responsive layout with a dark mode

**Personal**

- Ratings, a watched marker and a wishlist
- Activity log
- Statistics with interactive charts

**Administration**

- Two factor authentication with backup codes
- User and metadata management, including repair of missing covers and TMDb IDs
- A background service that completes actor profiles from TMDb
- Full export of database, cover art, backdrops and actor images as a ZIP archive

---

## Security and privacy

- All data stays on your server. Nothing leaves it except requests to TMDb when
  you import.
- Two factor authentication with backup codes.
- Sessions validated against IP subnet and user agent.
- Content Security Policy, CSRF protection, prepared statements, bcrypt hashing.
- Privacy policy and imprint pages included for GDPR compliance.

Security issues can be reported as described in [SECURITY.md](SECURITY.md).

---

## Installing without Docker

Requires PHP 8.2 or newer, Composer, Node.js with npm, a web server, and either
SQLite or MySQL/MariaDB.

```bash
git clone https://github.com/lunasans/MovieShelf.git
cd MovieShelf
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Configure the database connection in `.env` before migrating. The scheduler
needs a cron entry running `php artisan schedule:run` every minute, and the
queue needs `php artisan queue:work` kept alive by systemd or a similar
supervisor.

`update.sh` walks through updating such an installation step by step.

---

## Building the image yourself

```bash
git clone https://github.com/lunasans/MovieShelf.git
cd MovieShelf
docker build -t movieshelf .
```

---

## Technology

Laravel on PHP, Blade templates with Tailwind CSS built by Vite, Alpine.js for
interactivity, Chart.js for statistics, GLightbox for image viewing, and
GridStack for the arrangeable dashboard. Metadata comes from the TMDb API.

---

## Changes

Every version and its changes are listed under
[Releases](https://github.com/lunasans/MovieShelf/releases) and
[Tags](https://github.com/lunasans/MovieShelf/tags).

## Contributing

Contributions are welcome. Fork the repository, create a feature branch, commit
your changes and open a pull request.

## License

Intended for private use. See the LICENSE file for details.

## Author

[@lunasans](https://github.com/lunasans)

## Support

Open a [GitHub issue](https://github.com/lunasans/MovieShelf/issues) or use the
discussions in the repository.
