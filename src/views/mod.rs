//! The view layer: Sailfish templates are compiled into typed structs. This
//! module holds the shared context every page gets, the small helpers the
//! templates call, and the layout wrappers.

pub mod layouts;
pub mod pages;
pub mod ui;

use std::collections::BTreeMap;

use chrono::NaiveDateTime;
use sailfish::TemplateSimple;
use serde::Serialize;

use crate::http::AppError;
use crate::models::post::PostView;
use crate::models::user::User;
use crate::support::{dates, text};

/// Variables available to every template (what the Blade layouts pulled from
/// the request, session and config).
pub struct Base {
    pub csrf_token: String,
    pub auth_user: Option<User>,
    pub current_path: String,
    pub current_url: String,
    pub app_name: String,
    pub app_env: String,
    pub app_url: String,
    pub app_host: String,
    pub flash_success: Option<String>,
    pub flash_error: Option<String>,
    pub errors: Vec<String>,
    pub old: Old,
    pub year: String,
    /// `<link>`/`<script>` tags for the Vite bundle.
    pub vite_tags: String,
}

impl Base {
    /// Absolute URL for a site path (`url($path)`).
    pub fn url(&self, path: &str) -> String {
        if path.starts_with("http://") || path.starts_with("https://") {
            return path.to_string();
        }
        format!(
            "{}/{}",
            self.app_url.trim_end_matches('/'),
            path.trim_start_matches('/')
        )
    }

    /// `@csrf`.
    pub fn csrf_field(&self) -> String {
        format!(
            "<input type=\"hidden\" name=\"_token\" value=\"{}\">",
            esc(&self.csrf_token)
        )
    }

    pub fn user_name(&self) -> &str {
        self.auth_user
            .as_ref()
            .map(|u| u.name.as_str())
            .unwrap_or("User")
    }

    pub fn user_email(&self) -> &str {
        self.auth_user
            .as_ref()
            .map(|u| u.email.as_str())
            .unwrap_or("")
    }

    pub fn user_id(&self) -> Option<i64> {
        self.auth_user.as_ref().map(|u| u.id)
    }

    pub fn path_is(&self, path: &str) -> bool {
        self.current_path == path
    }

    pub fn path_starts(&self, prefix: &str) -> bool {
        self.current_path.starts_with(prefix)
    }
}

/// `@method('PUT')`.
pub fn method_field(method: &str) -> String {
    format!(
        "<input type=\"hidden\" name=\"_method\" value=\"{}\">",
        esc(method)
    )
}

/// Old input flashed back after a validation failure (`old('field', $default)`).
#[derive(Clone, Debug, Default)]
pub struct Old(pub BTreeMap<String, serde_json::Value>);

impl Old {
    pub fn is_empty(&self) -> bool {
        self.0.is_empty()
    }

    pub fn has(&self, key: &str) -> bool {
        self.0.contains_key(key)
    }

    pub fn value(&self, key: &str) -> Option<&serde_json::Value> {
        self.0.get(key)
    }

    /// The old string value, if one was flashed.
    pub fn get(&self, key: &str) -> Option<String> {
        match self.0.get(key)? {
            serde_json::Value::String(s) => Some(s.clone()),
            serde_json::Value::Null => None,
            other => Some(other.to_string()),
        }
    }

    /// `old($key, $fallback)` with Blade's empty-string-for-null semantics.
    pub fn or(&self, key: &str, fallback: Option<&str>) -> String {
        self.get(key)
            .or_else(|| fallback.map(|f| f.to_string()))
            .unwrap_or_default()
    }

    /// `old($key)` as a list of strings (checkbox groups such as `tags[]`).
    pub fn list(&self, key: &str) -> Option<Vec<String>> {
        let serde_json::Value::Array(items) = self.0.get(key)? else {
            return None;
        };
        Some(
            items
                .iter()
                .map(|v| {
                    v.as_str()
                        .map(|s| s.to_string())
                        .unwrap_or_else(|| v.to_string())
                })
                .collect(),
        )
    }
}

/// `<title>`, description, Open Graph / Twitter tags and optional JSON-LD.
#[derive(Clone, Debug)]
pub struct Seo {
    pub title: String,
    pub description: String,
    pub image: Option<String>,
    pub url: String,
    pub kind: String,
    pub twitter_title: String,
    pub twitter_description: String,
    pub twitter_image: Option<String>,
    pub canonical_url: String,
    pub json_ld: Option<String>,
}

impl Seo {
    pub fn new(base: &Base, title: impl Into<String>, description: impl Into<String>) -> Self {
        let title = title.into();
        let description = description.into();
        Self {
            twitter_title: title.clone(),
            twitter_description: description.clone(),
            title,
            description,
            image: None,
            url: base.current_url.clone(),
            kind: "website".into(),
            twitter_image: None,
            canonical_url: base.current_url.clone(),
            json_ld: None,
        }
    }

    pub fn site(base: &Base) -> Self {
        Self::new(base, base.app_name.clone(), "Thoughts, ideas and stories")
    }

    /// Fill the article-specific fields (`og:type`, images, canonical, JSON-LD).
    pub fn for_post(
        mut self,
        base: &Base,
        post: &PostView,
        image: Option<String>,
        twitter_image: Option<String>,
        canonical: String,
    ) -> Self {
        self.kind = if post.is_post() {
            "article".into()
        } else {
            "website".into()
        };
        self.twitter_image = twitter_image.or_else(|| image.clone());
        self.image = image;
        self.canonical_url = canonical;
        if post.is_post() {
            let mut json = serde_json::json!({
                "@context": "https://schema.org",
                "@type": "BlogPosting",
                "headline": post.title,
                "description": post.plain_excerpt,
                "url": base.url(&format!("/{}", post.slug)),
                "datePublished": post.published_at.map(dates::iso8601),
                "dateModified": post.updated_at.map(dates::iso8601),
                "publisher": {"@type": "Organization", "name": "SysAdmin Journal"},
            });
            if let Some(image) = post.feature_image.as_deref().filter(|i| !i.is_empty()) {
                json["image"] = serde_json::Value::String(base.url(image));
            }
            if let Some(author) = &post.primary_author {
                json["author"] = serde_json::json!({"@type": "Person", "name": author.name});
            }
            self.json_ld = Some(tojson(&json));
        }
        self
    }
}

/// Sidebar counts for the dashboard layout.
#[derive(Clone, Copy, Debug, Default)]
pub struct NavCounts {
    pub posts: i64,
    pub pages: i64,
    pub galleries: i64,
    pub tags: i64,
    pub users: i64,
}

// ---- helpers used from templates --------------------------------------------

/// PHP-style date formatting; empty string for `None`.
pub fn date(value: &Option<NaiveDateTime>, fmt: &str) -> String {
    value
        .map(|dt| dates::php_format(dt, fmt))
        .unwrap_or_default()
}

pub fn date_at(value: NaiveDateTime, fmt: &str) -> String {
    dates::php_format(value, fmt)
}

/// `$a ?? $b` for timestamps.
pub fn date_or(first: &Option<NaiveDateTime>, second: &Option<NaiveDateTime>, fmt: &str) -> String {
    date(&first.or(*second), fmt)
}

/// `Option<String>` as `&str` (null renders as nothing, like Blade).
pub fn s(value: &Option<String>) -> &str {
    value.as_deref().unwrap_or("")
}

/// JSON for Alpine `x-data` attributes. Meant for `<%= %>`, which escapes it
/// as an attribute value; the browser decodes the entities before Alpine runs.
pub fn js<T: Serialize>(value: &T) -> String {
    serde_json::to_string(value).unwrap_or_else(|_| "null".into())
}

/// Pretty JSON safe inside a `<script>` element.
pub fn tojson<T: Serialize>(value: &T) -> String {
    serde_json::to_string_pretty(value)
        .unwrap_or_else(|_| "null".into())
        .replace('<', "\\u003c")
        .replace('>', "\\u003e")
        .replace('&', "\\u0026")
}

pub fn esc(value: &str) -> String {
    text::escape(value)
}

pub fn initials(name: &str) -> String {
    text::initials(name)
}

pub fn plural(word: &str, count: i64) -> String {
    text::plural(word, count)
}

pub fn slug(value: &str) -> String {
    text::slug(value)
}

pub fn kb(bytes: u64) -> String {
    ((bytes as f64) / 1024.0).round().to_string()
}

pub fn pad2(n: usize) -> String {
    format!("{n:02}")
}

pub fn urlencode(value: &str) -> String {
    form_urlencoded::byte_serialize(value.as_bytes()).collect()
}

/// Render a template, mapping Sailfish errors onto `AppError`.
pub fn render<T: TemplateSimple>(template: T) -> Result<String, AppError> {
    template
        .render_once()
        .map_err(|e| AppError::Other(anyhow::anyhow!("template: {e}")))
}
