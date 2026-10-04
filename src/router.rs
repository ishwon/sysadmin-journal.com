use axum::Router;
use axum::extract::DefaultBodyLimit;
use axum::http::header;
use axum::middleware;
use axum::routing::{get, post};
use tower_cookies::CookieManagerLayer;
use tower_http::services::ServeDir;
use tower_http::set_header::SetResponseHeaderLayer;
use tower_http::trace::TraceLayer;
use tower_sessions::{Expiry, SessionManagerLayer};
use tower_sessions_sqlx_store::SqliteStore;

use crate::app::AppState;
use crate::handlers::{auth, dashboard, feed, public, search};
use crate::http::middleware::{
    csrf_and_method_override, immutable_cache, require_auth, trailing_slash_redirect,
};

const UPLOAD_LIMIT: usize = 220 * 1024 * 1024;

pub async fn build(state: AppState) -> anyhow::Result<Router> {
    let session_store = SqliteStore::new(state.db.clone());
    session_store.migrate().await?;
    let session_layer = SessionManagerLayer::new(session_store)
        .with_name("sysadmin_journal_session")
        .with_secure(state.config.app_url.starts_with("https://"))
        .with_same_site(tower_sessions::cookie::SameSite::Lax)
        .with_expiry(Expiry::OnInactivity(time::Duration::minutes(
            state.config.session_lifetime_minutes,
        )));

    let dashboard = Router::new()
        .route("/", get(dashboard::index))
        .route(
            "/posts",
            get(dashboard::posts::index).post(dashboard::posts::store),
        )
        .route("/posts/create", get(dashboard::posts::create))
        .route(
            "/posts/{id}",
            get(dashboard::posts::show)
                .put(dashboard::posts::update)
                .delete(dashboard::posts::destroy),
        )
        .route("/posts/{id}/edit", get(dashboard::posts::edit))
        .route(
            "/pages",
            get(dashboard::pages::index).post(dashboard::pages::store),
        )
        .route("/pages/create", get(dashboard::pages::create))
        .route(
            "/pages/{id}",
            put_delete(dashboard::pages::update, dashboard::pages::destroy),
        )
        .route("/pages/{id}/edit", get(dashboard::pages::edit))
        .route(
            "/galleries",
            get(dashboard::galleries::index).post(dashboard::galleries::store),
        )
        .route("/galleries/create", get(dashboard::galleries::create))
        .route(
            "/galleries/{id}",
            put_delete(dashboard::galleries::update, dashboard::galleries::destroy),
        )
        .route("/galleries/{id}/edit", get(dashboard::galleries::edit))
        .route(
            "/tags",
            get(dashboard::tags::index).post(dashboard::tags::store),
        )
        .route("/tags/create", get(dashboard::tags::create))
        .route(
            "/tags/{id}",
            put_delete(dashboard::tags::update, dashboard::tags::destroy),
        )
        .route("/tags/{id}/edit", get(dashboard::tags::edit))
        .route(
            "/users",
            get(dashboard::users::index).post(dashboard::users::store),
        )
        .route("/users/create", get(dashboard::users::create))
        .route(
            "/users/{id}",
            put_delete(dashboard::users::update, dashboard::users::destroy),
        )
        .route("/users/{id}/edit", get(dashboard::users::edit))
        .route("/media", get(dashboard::media::index))
        .route(
            "/media/directory",
            post(dashboard::media::create_directory).delete(dashboard::media::delete_directory),
        )
        .route("/media/upload", post(dashboard::media::upload))
        .route(
            "/media/photo",
            axum::routing::delete(dashboard::media::delete_photo),
        )
        .route("/sitemap/generate", post(dashboard::sitemap::generate))
        .route("/api/slug-check", post(dashboard::api::slug_check))
        .route("/api/upload-image", post(dashboard::api::upload_image))
        .layer(middleware::from_fn_with_state(state.clone(), require_auth))
        .layer(DefaultBodyLimit::max(UPLOAD_LIMIT));

    let build_dir = ServeDir::new(state.config.public_dir.join("build"))
        .precompressed_gzip()
        .precompressed_br();
    let media_dir = ServeDir::new(state.config.media_dir.clone());

    let routes = Router::new()
        .route("/login", get(auth::show_login).post(auth::login))
        .route("/logout", post(auth::logout))
        .nest("/dashboard", dashboard)
        .route("/", get(public::home))
        .route("/brand-system", get(public::brand_system))
        .route("/rss", get(feed::index))
        .route("/search", get(search::search))
        .route("/gallery", get(public::galleries))
        .route("/gallery/{slug}", get(public::gallery))
        .route("/tag/{slug}", get(public::tag))
        .route("/tag/{slug}/rss", get(feed::tag))
        .route("/author/{slug}", get(public::author))
        .route("/{slug}", get(public::show))
        .nest_service(
            "/build",
            tower::ServiceBuilder::new()
                .layer(SetResponseHeaderLayer::if_not_present(
                    header::CACHE_CONTROL,
                    immutable_cache(),
                ))
                .service(build_dir),
        )
        .nest_service("/content", media_dir)
        .with_state(state);

    // `Router::layer` runs middleware *after* routing, so `_method` spoofing has
    // to happen one level up: the outer router has no routes of its own and
    // hands every request to the routed app once the method has been rewritten.
    let app = Router::new()
        .fallback_service(routes)
        .layer(middleware::from_fn(csrf_and_method_override))
        .layer(session_layer)
        .layer(CookieManagerLayer::new())
        .layer(middleware::from_fn(trailing_slash_redirect))
        .layer(TraceLayer::new_for_http());

    Ok(app)
}

fn put_delete<H1, H2, T1, T2>(update: H1, destroy: H2) -> axum::routing::MethodRouter<AppState>
where
    H1: axum::handler::Handler<T1, AppState>,
    H2: axum::handler::Handler<T2, AppState>,
    T1: 'static,
    T2: 'static,
{
    axum::routing::put(update).delete(destroy)
}
