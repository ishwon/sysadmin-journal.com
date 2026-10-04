//! Test harness: an in-process app backed by a temporary SQLite database, with
//! a cookie jar so sessions, CSRF tokens and logins behave like a browser.

#![allow(dead_code)]

use std::collections::BTreeMap;
use std::path::PathBuf;

use axum::Router;
use axum::body::Body;
use axum::http::{HeaderMap, Request, StatusCode, header};
use chrono::NaiveDateTime;
use sysadmin_journal::app::AppState;
use sysadmin_journal::config::Config;
use sysadmin_journal::db::{self, Db};
use sysadmin_journal::models::post::{Post, PostInput};
use sysadmin_journal::models::tag::{Tag, TagInput};
use sysadmin_journal::models::user::{User, UserInput};
use sysadmin_journal::support::dates;
use tempfile::TempDir;
use tower::ServiceExt;

pub const BASE_URL: &str = "http://localhost";

pub struct TestApp {
    pub app: Router,
    pub db: Db,
    pub dir: TempDir,
    pub public_dir: PathBuf,
    cookies: BTreeMap<String, String>,
}

pub struct TestResponse {
    pub status: StatusCode,
    pub headers: HeaderMap,
    pub body: String,
}

impl TestResponse {
    pub fn location(&self) -> Option<&str> {
        self.headers
            .get(header::LOCATION)
            .and_then(|v| v.to_str().ok())
    }

    pub fn header(&self, name: &str) -> Option<&str> {
        self.headers.get(name).and_then(|v| v.to_str().ok())
    }

    pub fn assert_ok(&self) -> &Self {
        assert_eq!(
            self.status,
            StatusCode::OK,
            "body: {}",
            &self.body[..self.body.len().min(2000)]
        );
        self
    }

    pub fn assert_redirect(&self, to: &str) -> &Self {
        assert!(
            self.status.is_redirection(),
            "expected redirect, got {}",
            self.status
        );
        assert_eq!(self.location(), Some(to));
        self
    }

    pub fn assert_see(&self, text: &str) -> &Self {
        assert!(
            self.body.contains(text),
            "expected body to contain {text:?}"
        );
        self
    }

    pub fn assert_dont_see(&self, text: &str) -> &Self {
        assert!(
            !self.body.contains(text),
            "expected body not to contain {text:?}"
        );
        self
    }
}

impl TestApp {
    pub async fn new() -> Self {
        let dir = tempfile::tempdir().expect("temp dir");
        let db_path = dir.path().join("test.sqlite");
        let db = db::connect(&format!("sqlite:{}", db_path.display()))
            .await
            .expect("db");
        db::migrate(&db).await.expect("migrate");

        let public_dir = dir.path().join("public");
        let media_dir = dir.path().join("media");
        std::fs::create_dir_all(&public_dir).unwrap();
        std::fs::create_dir_all(media_dir.join("images")).unwrap();

        let config = Config {
            app_name: "SysAdmin Journal".into(),
            app_env: "testing".into(),
            app_url: BASE_URL.into(),
            host: "127.0.0.1".into(),
            port: 0,
            database_url: format!("sqlite:{}", db_path.display()),
            session_lifetime_minutes: 120,
            bcrypt_rounds: 4,
            public_dir: public_dir.clone(),
            media_dir,
            templates_dir: PathBuf::from(concat!(env!("CARGO_MANIFEST_DIR"), "/templates")),
            key: vec![9u8; 64],
        };
        let state = AppState::new(db.clone(), config);
        let app = sysadmin_journal::router::build(state)
            .await
            .expect("router");

        Self {
            app,
            db,
            dir,
            public_dir,
            cookies: BTreeMap::new(),
        }
    }

    fn cookie_header(&self) -> String {
        self.cookies
            .iter()
            .map(|(k, v)| format!("{k}={v}"))
            .collect::<Vec<_>>()
            .join("; ")
    }

    fn store_cookies(&mut self, headers: &HeaderMap) {
        for value in headers.get_all(header::SET_COOKIE) {
            let Ok(raw) = value.to_str() else { continue };
            let Some((pair, _attrs)) = raw.split_once(';').or(Some((raw, ""))) else {
                continue;
            };
            let Some((name, val)) = pair.split_once('=') else {
                continue;
            };
            let expired = raw.to_ascii_lowercase().contains("max-age=0");
            if val.is_empty() || expired {
                self.cookies.remove(name.trim());
            } else {
                self.cookies
                    .insert(name.trim().to_string(), val.trim().to_string());
            }
        }
    }

    pub async fn send(&mut self, request: Request<Body>) -> TestResponse {
        let mut request = request;
        if !self.cookies.is_empty() {
            request
                .headers_mut()
                .insert(header::COOKIE, self.cookie_header().parse().unwrap());
        }
        let response = self.app.clone().oneshot(request).await.expect("response");
        let status = response.status();
        let headers = response.headers().clone();
        self.store_cookies(&headers);
        let bytes = axum::body::to_bytes(response.into_body(), usize::MAX)
            .await
            .expect("body");
        TestResponse {
            status,
            headers,
            body: String::from_utf8_lossy(&bytes).into_owned(),
        }
    }

    pub async fn get(&mut self, path: &str) -> TestResponse {
        self.send(Request::builder().uri(path).body(Body::empty()).unwrap())
            .await
    }

    /// POST an `application/x-www-form-urlencoded` body. The CSRF token is added automatically.
    pub async fn post(&mut self, path: &str, fields: &[(&str, &str)]) -> TestResponse {
        let token = self.csrf_token().await;
        let mut body = form_urlencoded::Serializer::new(String::new());
        body.append_pair("_token", &token);
        for (k, v) in fields {
            body.append_pair(k, v);
        }
        let request = Request::builder()
            .method("POST")
            .uri(path)
            .header(header::CONTENT_TYPE, "application/x-www-form-urlencoded")
            .body(Body::from(body.finish()))
            .unwrap();
        self.send(request).await
    }

    /// `PUT` through `_method` spoofing, like the Blade forms do.
    pub async fn put(&mut self, path: &str, fields: &[(&str, &str)]) -> TestResponse {
        let mut all = vec![("_method", "PUT")];
        all.extend_from_slice(fields);
        self.post(path, &all).await
    }

    pub async fn delete(&mut self, path: &str) -> TestResponse {
        self.post(path, &[("_method", "DELETE")]).await
    }

    /// The session's CSRF token (visiting the login form creates one).
    pub async fn csrf_token(&mut self) -> String {
        let page = self.get("/login").await;
        let page = if page.status.is_redirection() {
            self.get("/dashboard").await
        } else {
            page
        };
        extract(&page.body, "name=\"csrf-token\" content=\"")
            .or_else(|| extract(&page.body, "name=\"_token\" value=\""))
            .expect("csrf token in page")
    }

    /// `actingAs(User::factory()->create())`: creates a user and signs in.
    pub async fn acting_as_new_user(&mut self) -> User {
        let user = create_user(&self.db, "Test User", "test-user").await;
        let response = self
            .post(
                "/login",
                &[("email", &user.email), ("password", "password")],
            )
            .await;
        response.assert_redirect("/dashboard");
        user
    }
}

fn extract(body: &str, marker: &str) -> Option<String> {
    let start = body.find(marker)? + marker.len();
    let end = body[start..].find('"')? + start;
    Some(body[start..end].to_string())
}

pub fn url(path: &str) -> String {
    format!("{BASE_URL}/{}", path.trim_start_matches('/'))
}

pub fn now() -> NaiveDateTime {
    dates::now()
}

pub fn days_ago(days: i64) -> NaiveDateTime {
    dates::now() - chrono::Duration::days(days)
}

/// `Post::factory()->create([...])`.
pub fn post_input(title: &str) -> PostInput {
    PostInput {
        title: title.to_string(),
        slug: sysadmin_journal::support::text::slug(title),
        html: Some("<p>Lorem ipsum dolor sit amet.</p>".into()),
        plaintext: Some("Lorem ipsum dolor sit amet.".into()),
        kind: "post".into(),
        status: "draft".into(),
        reading_time: 1,
        ..Default::default()
    }
}

pub fn published(mut input: PostInput) -> PostInput {
    input.status = "published".into();
    input.published_at = Some(days_ago(1));
    input
}

pub fn page(mut input: PostInput) -> PostInput {
    input.kind = "page".into();
    input
}

pub async fn create_post(db: &Db, input: PostInput) -> Post {
    let id = Post::create(db, &input).await.expect("create post");
    Post::find(db, id).await.unwrap().unwrap()
}

pub async fn create_tag(db: &Db, name: &str, slug: &str) -> Tag {
    let id = Tag::create(
        db,
        &TagInput {
            name: name.into(),
            slug: slug.into(),
            ..Default::default()
        },
    )
    .await
    .expect("create tag");
    Tag::find(db, id).await.unwrap().unwrap()
}

/// Password is always `password`.
pub async fn create_user(db: &Db, name: &str, slug: &str) -> User {
    let email = format!("{slug}@example.com");
    let id = User::create(
        db,
        &UserInput {
            name: name.into(),
            slug: slug.into(),
            email,
            password: Some(bcrypt::hash("password", 4).unwrap()),
            ..Default::default()
        },
    )
    .await
    .expect("create user");
    User::find(db, id).await.unwrap().unwrap()
}
