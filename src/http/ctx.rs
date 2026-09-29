//! Per-request context: session, authenticated user, CSRF token, flash data and
//! the helpers handlers use to render templates or redirect.

use std::collections::BTreeMap;

use axum::extract::FromRequestParts;
use axum::http::header::REFERER;
use axum::http::request::Parts;
use axum::response::{Html, IntoResponse, Redirect, Response};
use minijinja::value::Value;
use tower_cookies::Cookies;
use tower_sessions::Session;

use super::error::AppError;
use super::flash::Flash;
use super::form::FormData;
use crate::app::AppState;
use crate::models::gallery::Gallery;
use crate::models::post::PostQuery;
use crate::models::tag::Tag;
use crate::models::user::User;
use crate::support::text;

/// The authenticated user, placed into request extensions by the auth middleware.
#[derive(Clone, Debug)]
pub struct AuthUser(pub User);

pub struct Ctx {
    pub state: AppState,
    pub session: Session,
    pub cookies: Cookies,
    pub user: Option<User>,
    pub csrf: String,
    pub path: String,
    pub query: Vec<(String, String)>,
    pub flash: Flash,
    referer: Option<String>,
}

impl FromRequestParts<AppState> for Ctx {
    type Rejection = AppError;

    async fn from_request_parts(
        parts: &mut Parts,
        state: &AppState,
    ) -> Result<Self, Self::Rejection> {
        let session = Session::from_request_parts(parts, state)
            .await
            .map_err(|(_, msg)| AppError::Other(anyhow::anyhow!("session: {msg}")))?;
        let cookies = Cookies::from_request_parts(parts, state)
            .await
            .map_err(|(_, msg)| AppError::Other(anyhow::anyhow!("cookies: {msg}")))?;

        let user = match parts.extensions.get::<AuthUser>() {
            Some(AuthUser(user)) => Some(user.clone()),
            None => match session.get::<i64>("user_id").await? {
                Some(id) => User::find(&state.db, id).await?,
                None => None,
            },
        };

        let csrf = match session.get::<String>("_token").await? {
            Some(token) => token,
            None => {
                let token = text::random(40);
                session.insert("_token", &token).await?;
                token
            }
        };

        let flash = session
            .remove::<Flash>(Flash::KEY)
            .await?
            .unwrap_or_default();

        // Inside the nested `/dashboard` router `parts.uri` has the prefix stripped.
        let uri = parts
            .extensions
            .get::<axum::extract::OriginalUri>()
            .map(|o| o.0.clone())
            .unwrap_or_else(|| parts.uri.clone());
        let path = uri.path().to_string();
        let query: Vec<(String, String)> = uri
            .query()
            .map(|q| form_urlencoded::parse(q.as_bytes()).into_owned().collect())
            .unwrap_or_default();
        let referer = parts
            .headers
            .get(REFERER)
            .and_then(|v| v.to_str().ok())
            .map(|s| s.to_string());

        Ok(Self {
            state: state.clone(),
            session,
            cookies,
            user,
            csrf,
            path,
            query,
            flash,
            referer,
        })
    }
}

impl Ctx {
    pub fn db(&self) -> &crate::db::Db {
        &self.state.db
    }

    pub fn query_value(&self, key: &str) -> Option<&str> {
        self.query
            .iter()
            .find(|(k, _)| k == key)
            .map(|(_, v)| v.as_str())
    }

    pub fn page(&self) -> i64 {
        crate::support::pagination::page_from(self.query_value("page"))
    }

    /// The authenticated user, or a 403 when there is none.
    pub fn auth(&self) -> Result<&User, AppError> {
        self.user.as_ref().ok_or(AppError::Forbidden)
    }

    /// Variables available to every template (`csrf_token`, `auth_user`, …).
    fn base_context(&self) -> Value {
        let config = &self.state.config;
        minijinja::context! {
            csrf_token => self.csrf.clone(),
            auth_user => self.user.clone(),
            current_path => self.path.clone(),
            current_url => config.url(&self.path),
            app_name => config.app_name.clone(),
            app_env => config.app_env.clone(),
            app_url => config.app_url.clone(),
            app_host => config.host_name(),
            flash_success => self.flash.success.clone(),
            flash_error => self.flash.error.clone(),
            errors => self.flash.errors.clone(),
            old => self.flash.old.clone(),
            year => crate::support::dates::now().format("%Y").to_string(),
        }
    }

    pub fn render(&self, name: &str, ctx: Value) -> Result<Response, AppError> {
        let merged = minijinja::context! { ..ctx, ..self.base_context() };
        let html = self.state.templates.render(name, merged)?;
        Ok(Html(html).into_response())
    }

    /// Render a dashboard page: adds the sidebar counts the layout shows.
    pub async fn render_dashboard(&self, name: &str, ctx: Value) -> Result<Response, AppError> {
        let db = self.db();
        let nav_counts = minijinja::context! {
            posts => PostQuery::new().posts().count(db).await?,
            pages => PostQuery::new().pages().count(db).await?,
            galleries => Gallery::count(db).await?,
            tags => Tag::count(db).await?,
            users => User::count(db).await?,
        };
        self.render(name, minijinja::context! { nav_counts, ..ctx })
    }

    /// Render a template to a string with the base context (feeds, sitemaps).
    pub fn render_string(&self, name: &str, ctx: Value) -> Result<String, AppError> {
        let merged = minijinja::context! { ..ctx, ..self.base_context() };
        Ok(self.state.templates.render(name, merged)?)
    }

    async fn put_flash(&self, flash: Flash) -> Result<(), AppError> {
        if !flash.is_empty() {
            self.session.insert(Flash::KEY, &flash).await?;
        }
        Ok(())
    }

    /// `redirect()->route(...)->with('success', $message)`.
    pub async fn redirect_with_success(
        &self,
        to: &str,
        message: &str,
    ) -> Result<Response, AppError> {
        self.put_flash(Flash {
            success: Some(message.to_string()),
            ..Default::default()
        })
        .await?;
        Ok(Redirect::to(to).into_response())
    }

    /// `back()->withErrors($errors)->withInput()`.
    pub async fn back_with_errors(
        &self,
        errors: Vec<String>,
        form: Option<&FormData>,
    ) -> Result<Response, AppError> {
        let old: BTreeMap<String, serde_json::Value> = form.map(|f| f.to_old()).unwrap_or_default();
        self.put_flash(Flash {
            errors,
            old,
            ..Default::default()
        })
        .await?;
        Ok(Redirect::to(&self.back_url()).into_response())
    }

    /// `back()`: the referer, or the current path as a fallback.
    pub fn back_url(&self) -> String {
        match &self.referer {
            Some(url) => url.clone(),
            None => self.path.clone(),
        }
    }

    /// `back()->with('success', ...)`.
    pub async fn back_with_success(&self, message: &str) -> Result<Response, AppError> {
        self.redirect_with_success(&self.back_url(), message).await
    }
}
