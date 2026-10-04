# SysAdmin Journal

The blog and backoffice behind [sysadmin-journal.com](https://sysadmin-journal.com), written in Rust.
A single binary serves the public site (posts, pages, tags, authors, galleries, search, RSS) and the
dashboard (markdown/HTML editor with live preview, media library, galleries, tags, users, sitemap).

Stack: [axum](https://github.com/tokio-rs/axum) · [sqlx](https://github.com/launchbadge/sqlx) (SQLite) ·
[minijinja](https://github.com/mitsuhiko/minijinja) templates · Tailwind CSS v4 and Alpine.js bundled with Vite.

## Requirements

- Rust 1.85+ (edition 2024)
- Node.js 20+ (only to build the CSS/JS bundle)

## Setup

```bash
cp .env.example .env
# set APP_KEY (openssl rand -base64 32) and APP_URL

npm install
npm run build              # writes public/build/ (Tailwind + Alpine + marked)

cargo run -- migrate       # creates database/database.sqlite and applies the schema
cargo run -- user-create --name "Ish Sookun" --email you@example.com --password 'secret'
cargo run -- serve         # http://127.0.0.1:8000
```

`serve` is the default subcommand, so `cargo run` alone also starts the server. Migrations run on start-up.

### Development

```bash
npm run dev                # Vite dev server; the app picks it up through public/hot
APP_ENV=local cargo run    # APP_ENV=local reloads templates/ from disk on every request
```

### Console commands

| Command | Replaces |
| --- | --- |
| `sysadmin-journal serve` | `php artisan serve` / Octane |
| `sysadmin-journal migrate` | `php artisan migrate` |
| `sysadmin-journal ghost-import [path]` | `php artisan ghost:import` |
| `sysadmin-journal posts-wrap-figures [--dry-run]` | `php artisan posts:wrap-figures` |
| `sysadmin-journal user-create --name … --email … --password … [--slug …]` | database seeder / tinker |

## Configuration

Everything is read from the environment (a `.env` file is loaded if present). See `.env.example`.

| Variable | Default | Purpose |
| --- | --- | --- |
| `APP_KEY` | *(required in production)* | Signs the remember-me cookie. Laravel `base64:` keys are accepted. |
| `APP_URL` | `http://localhost:8000` | Absolute URLs in feeds, sitemaps and meta tags. |
| `APP_ENV` | `production` | `local` enables template reloading. |
| `APP_HOST` / `APP_PORT` | `127.0.0.1` / `8000` | Bind address. |
| `DATABASE_URL` | `sqlite:database/database.sqlite` | SQLite file. An existing Laravel-created database works as is. |
| `SESSION_LIFETIME` | `120` | Minutes of inactivity before a session expires. |
| `BCRYPT_ROUNDS` | `12` | Password hashing cost (existing `$2y$` hashes verify unchanged). |
| `PUBLIC_DIR` | `public` | Static files, the Vite bundle and generated sitemaps. |
| `MEDIA_DIR` | `storage/app/public` | Uploaded media, served under `/content/`. |
| `TEMPLATES_DIR` | `templates` | Only used when `APP_ENV=local`; templates are otherwise embedded in the binary. |

## Layout

```
src/            application (router, handlers, models, middleware, CLI commands)
templates/      minijinja views (the former Blade views, same structure)
migrations/     SQL schema, applied with sqlx
resources/      Tailwind entry point and JavaScript bundled by Vite
public/         favicon, robots.txt, generated sitemaps, built assets (public/build)
storage/app/public/images   media library root (served as /content/images/...)
tests/          integration tests driving the router in-process
ghost-nice/     the Ghost export used by `ghost-import`
```

## Deployment

Build a release binary and ship it with `public/` (after `npm run build`) and a writable
`storage/app/public` and `database/` directory:

```bash
npm ci && npm run build
cargo build --release
./target/release/sysadmin-journal serve
```

Put it behind nginx/Caddy for TLS. The binary serves everything itself (`/build/*` with immutable
cache headers, `/content/*` from the media directory, sitemaps from `public/`), so no rewrite rules are needed.
`APP_URL` must be `https://…` in production so the session cookie is marked secure.

Example systemd unit:

```ini
[Service]
WorkingDirectory=/srv/sysadmin-journal
EnvironmentFile=/srv/sysadmin-journal/.env
ExecStart=/srv/sysadmin-journal/sysadmin-journal serve
Restart=on-failure
```

## Tests

```bash
cargo test
```
