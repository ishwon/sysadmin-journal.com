# SysAdmin Journal — project guidelines

This is a Rust web application (axum + sqlx/SQLite + Sailfish templates). It replaced a Laravel app; the
templates and routes intentionally mirror the old Blade views and Laravel routes one to one.

## Layout

- `src/router.rs` — every route. Dashboard routes live under `/dashboard` behind the `require_auth` middleware.
- `src/handlers/` — request handlers (`public.rs`, `feed.rs`, `search.rs`, `auth.rs`, `dashboard/*`).
- `src/models/` — `sqlx` row structs and queries. `PostQuery` mirrors the old Eloquent scopes (`posts()`, `pages()`, `published()`).
- `src/http/` — `Ctx` request context (session, auth user, CSRF token, flash, `base()`, `page()`, `dashboard()`), middleware, form parsing, validation.
- `src/views/` — the view layer: `Base` context and template helpers (`mod.rs`), layouts, one struct per page (`pages.rs`), and the UI component builders (`ui.rs`).
- `src/support/` — markdown (CommonMark + figure captions), PHP-style date formatting, string helpers, pagination, Vite manifest.
- `src/commands.rs` — CLI subcommands (Ghost import, wrap-figures, user creation).
- `templates/` — Sailfish `.stpl` templates, compiled into the binary by the `TemplateSimple` derives in `src/views`.
- `migrations/` — idempotent SQL applied by `sqlx::migrate!` on start-up.
- `resources/css/app.css`, `resources/js/app.js` — Tailwind v4 theme and the Alpine.js bundle built by Vite into `public/build`.

## Conventions

- Handlers take `Ctx` and return `Result<Response, AppError>`. Build the page struct from `src/views/pages.rs`,
  render it with `views::render`, then wrap it with `ctx.page(&base, &seo, feed_url, content)` (public) or
  `ctx.dashboard(&base, DashboardPage::new(title, content)...)` (adds the sidebar counts). Redirect with flash via
  `ctx.redirect_with_success` / `ctx.back_with_errors`.
- Forms are parsed with `FormData` (supports `tags[]` and `images[0][path]`), validated with `Validator`
  (Laravel-style messages), and re-rendered with `old.*` and `errors` from the flash.
- HTML forms POST with `_method=PUT|DELETE` (`method_field()` in templates); CSRF uses `_token` /
  `X-CSRF-TOKEN`. Multipart handlers verify the token themselves.
- Timestamps are stored as `YYYY-MM-DD HH:MM:SS` UTC strings (compatible with the old data). Use
  `support::dates` and the `date("j F Y")` template filter with PHP format characters.
- Templates: `<%= %>` escapes, `<%- %>` is raw (only for stored HTML such as `post.html` and for component output).
  `js(&value)` inside `<%= %>` JSON-encodes values for Alpine `x-data` attributes. Partials are pulled in with
  `include!` and see the parent's variables. Optional fields are `Option`: use `s(&field)` or `if let`.
- UI components live in `src/views/ui.rs` (`ui::button(..).html(..)`, `ui::input(..).html()`, `ui::card().open()/close()`).
  Reuse them rather than hand-writing the same markup.

## Workflow

- `cargo build` / `cargo test` must pass. Run `cargo fmt` and `cargo clippy` before committing.
- Frontend changes: `npm run build` (or `npm run dev` while developing). Template changes need a `cargo build`,
  since templates are compiled into the binary.
- Do not add documentation files unless asked. Do not change dependencies without approval.
