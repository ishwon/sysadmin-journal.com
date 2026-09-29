# SysAdmin Journal — project guidelines

This is a Rust web application (axum + sqlx/SQLite + minijinja). It replaced a Laravel app; the
templates and routes intentionally mirror the old Blade views and Laravel routes one to one.

## Layout

- `src/router.rs` — every route. Dashboard routes live under `/dashboard` behind the `require_auth` middleware.
- `src/handlers/` — request handlers (`public.rs`, `feed.rs`, `search.rs`, `auth.rs`, `dashboard/*`).
- `src/models/` — `sqlx` row structs and queries. `PostQuery` mirrors the old Eloquent scopes (`posts()`, `pages()`, `published()`).
- `src/http/` — `Ctx` request context (session, auth user, CSRF token, flash, `render`), middleware, form parsing, validation.
- `src/support/` — markdown (CommonMark + figure captions), PHP-style date formatting, string helpers, pagination, Vite manifest.
- `src/commands.rs` — CLI subcommands (Ghost import, wrap-figures, user creation).
- `templates/` — minijinja templates; `components/ui.html` holds the UI macros (button, card, input, modal…).
- `migrations/` — idempotent SQL applied by `sqlx::migrate!` on start-up.
- `resources/css/app.css`, `resources/js/app.js` — Tailwind v4 theme and the Alpine.js bundle built by Vite into `public/build`.

## Conventions

- Handlers take `Ctx` and return `Result<Response, AppError>`. Render with `ctx.render(...)` (public) or
  `ctx.render_dashboard(...)` (adds sidebar counts). Redirect with flash via `ctx.redirect_with_success` /
  `ctx.back_with_errors`.
- Forms are parsed with `FormData` (supports `tags[]` and `images[0][path]`), validated with `Validator`
  (Laravel-style messages), and re-rendered with `old.*` and `errors` from the flash.
- HTML forms POST with `_method=PUT|DELETE` (`method_field()` in templates); CSRF uses `_token` /
  `X-CSRF-TOKEN`. Multipart handlers verify the token themselves.
- Timestamps are stored as `YYYY-MM-DD HH:MM:SS` UTC strings (compatible with the old data). Use
  `support::dates` and the `date("j F Y")` template filter with PHP format characters.
- Templates autoescape HTML; use `|safe` only for stored HTML (`post.html`, excerpts). `|js` JSON-encodes values
  for Alpine `x-data` attributes.
- Keep new templates consistent with the existing ones: import `components/ui.html` as `ui` and use its macros.

## Workflow

- `cargo build` / `cargo test` must pass. Run `cargo fmt` and `cargo clippy` before committing.
- Frontend changes: `npm run build` (or `npm run dev` while developing). Template changes are picked up without a
  rebuild when `APP_ENV=local`; otherwise rebuild, since templates are embedded in the binary.
- Do not add documentation files unless asked. Do not change dependencies without approval.
