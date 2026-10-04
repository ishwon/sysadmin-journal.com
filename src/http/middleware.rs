use axum::body::{Body, to_bytes};
use axum::extract::{Request, State};
use axum::http::{HeaderValue, Method, StatusCode, header};
use axum::middleware::Next;
use axum::response::{IntoResponse, Redirect, Response};
use tower_cookies::{Cookie, Cookies};
use tower_sessions::Session;

use super::ctx::AuthUser;
use super::error::error_page;
use crate::app::AppState;
use crate::models::user::User;

pub const REMEMBER_COOKIE: &str = "remember_web";
const FORM_LIMIT: usize = 4 * 1024 * 1024;

/// Apache's `RewriteRule` from the old `.htaccess`: redirect `/path/` to `/path`.
pub async fn trailing_slash_redirect(req: Request, next: Next) -> Response {
    let path = req.uri().path();
    if path.len() > 1 && path.ends_with('/') {
        let trimmed = path.trim_end_matches('/');
        let target = match req.uri().query() {
            Some(q) => format!("{trimmed}?{q}"),
            None => trimmed.to_string(),
        };
        return (StatusCode::MOVED_PERMANENTLY, [(header::LOCATION, target)]).into_response();
    }
    next.run(req).await
}

/// `_method` form spoofing (`@method('PUT')`) and CSRF verification for every
/// state-changing request, like Laravel's `VerifyCsrfToken` middleware.
///
/// Multipart bodies are not buffered here; the upload handlers verify the
/// `_token` part themselves.
pub async fn csrf_and_method_override(req: Request, next: Next) -> Response {
    if matches!(*req.method(), Method::GET | Method::HEAD | Method::OPTIONS) {
        return next.run(req).await;
    }

    let content_type = req
        .headers()
        .get(header::CONTENT_TYPE)
        .and_then(|v| v.to_str().ok())
        .unwrap_or("")
        .to_ascii_lowercase();
    let session = req.extensions().get::<Session>().cloned();
    let expected = match &session {
        Some(session) => session.get::<String>("_token").await.ok().flatten(),
        None => None,
    };

    let header_token = req
        .headers()
        .get("x-csrf-token")
        .and_then(|v| v.to_str().ok())
        .map(|s| s.to_string());

    if content_type.starts_with("application/x-www-form-urlencoded") {
        let (mut parts, body) = req.into_parts();
        let bytes = match to_bytes(body, FORM_LIMIT).await {
            Ok(b) => b,
            Err(_) => return error_page(StatusCode::PAYLOAD_TOO_LARGE, "Payload Too Large"),
        };
        let mut form_token = None;
        let mut method_override = None;
        for (k, v) in form_urlencoded::parse(&bytes) {
            match k.as_ref() {
                "_token" => form_token = Some(v.into_owned()),
                "_method" => method_override = Some(v.to_ascii_uppercase()),
                _ => {}
            }
        }
        if !token_matches(expected.as_deref(), form_token.or(header_token).as_deref()) {
            return error_page(
                StatusCode::from_u16(419).unwrap_or(StatusCode::FORBIDDEN),
                "Page Expired",
            );
        }
        if parts.method == Method::POST
            && let Some(m) = method_override
            && let Ok(method) = Method::from_bytes(m.as_bytes())
            && matches!(method, Method::PUT | Method::PATCH | Method::DELETE)
        {
            parts.method = method;
        }
        let req = Request::from_parts(parts, Body::from(bytes));
        return next.run(req).await;
    }

    if content_type.starts_with("multipart/form-data") {
        return next.run(req).await;
    }

    if !token_matches(expected.as_deref(), header_token.as_deref()) {
        return error_page(
            StatusCode::from_u16(419).unwrap_or(StatusCode::FORBIDDEN),
            "Page Expired",
        );
    }
    next.run(req).await
}

pub fn token_matches(expected: Option<&str>, given: Option<&str>) -> bool {
    match (expected, given) {
        (Some(e), Some(g)) => {
            !e.is_empty()
                && e.len() == g.len()
                && e.bytes()
                    .zip(g.bytes())
                    .fold(0u8, |acc, (a, b)| acc | (a ^ b))
                    == 0
        }
        _ => false,
    }
}

/// `auth` middleware: log the user in from the session (or the remember-me
/// cookie), otherwise remember the intended URL and send them to the login form.
pub async fn require_auth(
    State(state): State<AppState>,
    session: Session,
    cookies: Cookies,
    mut req: Request,
    next: Next,
) -> Response {
    let user = match session.get::<i64>("user_id").await.ok().flatten() {
        Some(id) => User::find(&state.db, id).await.ok().flatten(),
        None => None,
    };

    let user = match user {
        Some(user) => Some(user),
        None => match user_from_remember_cookie(&state, &cookies).await {
            Some(user) => {
                if session.insert("user_id", user.id).await.is_err() {
                    None
                } else {
                    Some(user)
                }
            }
            None => None,
        },
    };

    match user {
        Some(user) => {
            req.extensions_mut().insert(AuthUser(user));
            next.run(req).await
        }
        None => {
            let uri = req
                .extensions()
                .get::<axum::extract::OriginalUri>()
                .map(|o| o.0.clone())
                .unwrap_or_else(|| req.uri().clone());
            let intended = match uri.query() {
                Some(q) => format!("{}?{q}", uri.path()),
                None => uri.path().to_string(),
            };
            let _ = session.insert("intended", intended).await;
            Redirect::to("/login").into_response()
        }
    }
}

async fn user_from_remember_cookie(state: &AppState, cookies: &Cookies) -> Option<User> {
    let cookie = cookies.signed(&state.cookie_key).get(REMEMBER_COOKIE)?;
    let (id, token) = cookie.value().split_once('|')?;
    let id: i64 = id.parse().ok()?;
    let user = User::find(&state.db, id).await.ok().flatten()?;
    match &user.remember_token {
        Some(stored) if token_matches(Some(stored), Some(token)) => Some(user),
        _ => None,
    }
}

pub fn remember_cookie(value: String) -> Cookie<'static> {
    Cookie::build((REMEMBER_COOKIE, value))
        .path("/")
        .http_only(true)
        .same_site(tower_cookies::cookie::SameSite::Lax)
        .max_age(time::Duration::days(365 * 5))
        .build()
}

pub fn forget_cookie() -> Cookie<'static> {
    Cookie::build((REMEMBER_COOKIE, ""))
        .path("/")
        .http_only(true)
        .max_age(time::Duration::ZERO)
        .build()
}

/// Cache-friendly header for immutable build assets.
pub fn immutable_cache() -> HeaderValue {
    HeaderValue::from_static("public, max-age=31536000, immutable")
}
