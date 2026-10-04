//! minijinja environment: template loading, filters and functions the views use.

use std::path::Path;
use std::sync::Arc;

use include_dir::{Dir, include_dir};
use minijinja::value::{Kwargs, Value};
use minijinja::{AutoEscape, Environment, Error, ErrorKind, State};

use crate::support::{dates, text, vite::Vite};

static EMBEDDED: Dir<'static> = include_dir!("$CARGO_MANIFEST_DIR/templates");

#[derive(Clone)]
pub struct Templates {
    env: Arc<Environment<'static>>,
    dir: Option<std::path::PathBuf>,
    vite: Arc<Vite>,
    app_url: String,
}

impl Templates {
    /// Templates are compiled into the binary. When `dir` points at an existing
    /// directory (local development) it is read on every request instead, so
    /// edits show up without a rebuild.
    pub fn new(dir: Option<&Path>, vite: Vite, app_url: &str) -> Self {
        let vite = Arc::new(vite);
        let dir = dir.filter(|d| d.is_dir()).map(|d| d.to_path_buf());
        let env = Arc::new(build_env(dir.as_deref(), vite.clone(), app_url));
        Self {
            env,
            dir,
            vite,
            app_url: app_url.to_string(),
        }
    }

    fn env(&self) -> Arc<Environment<'static>> {
        match &self.dir {
            Some(dir) => Arc::new(build_env(Some(dir), self.vite.clone(), &self.app_url)),
            None => self.env.clone(),
        }
    }

    pub fn render(&self, name: &str, ctx: Value) -> Result<String, Error> {
        self.env().get_template(name)?.render(ctx)
    }
}

fn build_env(dir: Option<&Path>, vite: Arc<Vite>, app_url: &str) -> Environment<'static> {
    let mut env = Environment::new();
    match dir {
        Some(dir) => env.set_loader(minijinja::path_loader(dir)),
        None => env.set_loader(|name| {
            Ok(EMBEDDED
                .get_file(name)
                .and_then(|f| f.contents_utf8())
                .map(|s| s.to_string()))
        }),
    }
    env.set_auto_escape_callback(|_| AutoEscape::Html);
    env.set_undefined_behavior(minijinja::UndefinedBehavior::Chainable);
    env.set_formatter(blade_formatter);
    env.set_trim_blocks(true);
    env.set_lstrip_blocks(true);

    let base = app_url.to_string();

    env.add_filter("date", date_filter);
    env.add_filter("js", js_filter);
    env.add_filter("slug", |v: String| text::slug(&v));
    env.add_filter("plural", |word: String, count: i64| {
        text::plural(&word, count)
    });
    env.add_filter("initials", |v: String| text::initials(&v));
    env.add_filter("strip_tags", |v: String| text::strip_tags(&v));
    env.add_filter("limit", |v: String, n: usize| text::limit(&v, n));
    env.add_filter("kb", |bytes: f64| {
        format!("{}", (bytes / 1024.0).round() as i64)
    });
    env.add_filter("pad2", |n: i64| format!("{n:02}"));
    let url_base = base.clone();
    env.add_filter("url", move |v: String| absolute_url(&url_base, &v));

    let vite_tags = vite.clone();
    env.add_function("vite", move |entries: Vec<String>| -> Value {
        let refs: Vec<&str> = entries.iter().map(|s| s.as_str()).collect();
        Value::from_safe_string(vite_tags.tags(&refs))
    });
    let url_base = base.clone();
    env.add_function("url", move |path: Option<String>| {
        absolute_url(&url_base, path.as_deref().unwrap_or("/"))
    });
    env.add_function("csrf_field", |state: &State| -> Result<Value, Error> {
        let token = state
            .lookup("csrf_token")
            .map(|v| v.to_string())
            .unwrap_or_default();
        Ok(Value::from_safe_string(format!(
            "<input type=\"hidden\" name=\"_token\" value=\"{}\">",
            text::escape(&token)
        )))
    });
    env.add_function("method_field", |method: String| -> Value {
        Value::from_safe_string(format!(
            "<input type=\"hidden\" name=\"_method\" value=\"{}\">",
            text::escape(&method)
        ))
    });
    env.add_function("now", |kwargs: Kwargs| -> Result<String, Error> {
        let fmt: Option<String> = kwargs.get("format")?;
        kwargs.assert_all_used()?;
        Ok(match fmt {
            Some(f) => dates::php_format(dates::now(), &f),
            None => dates::to_db(dates::now()),
        })
    });
    env.add_function("hour", || {
        dates::now()
            .format("%H")
            .to_string()
            .parse::<i64>()
            .unwrap_or(0)
    });

    env
}

/// Output formatting with Blade semantics: `null` prints as nothing, and
/// escaping covers `& < > " '` only (minijinja's default also encodes `/`,
/// which mangles URLs in the RSS and sitemap templates).
fn blade_formatter(out: &mut minijinja::Output, state: &State, value: &Value) -> Result<(), Error> {
    if value.is_undefined() || value.is_none() {
        return Ok(());
    }
    if value.is_safe() || matches!(state.auto_escape(), AutoEscape::None) {
        return write!(out, "{value}").map_err(Error::from);
    }
    let raw = value.to_string();
    let mut escaped = String::with_capacity(raw.len());
    for c in raw.chars() {
        match c {
            '&' => escaped.push_str("&amp;"),
            '<' => escaped.push_str("&lt;"),
            '>' => escaped.push_str("&gt;"),
            '"' => escaped.push_str("&quot;"),
            '\'' => escaped.push_str("&#x27;"),
            _ => escaped.push(c),
        }
    }
    write!(out, "{escaped}").map_err(Error::from)
}

fn absolute_url(base: &str, path: &str) -> String {
    if path.starts_with("http://") || path.starts_with("https://") {
        return path.to_string();
    }
    format!(
        "{}/{}",
        base.trim_end_matches('/'),
        path.trim_start_matches('/')
    )
}

/// `{{ value|date("j F Y") }}` with PHP format characters. Empty for null.
fn date_filter(value: Value, fmt: Option<String>) -> Result<String, Error> {
    if value.is_none() || value.is_undefined() {
        return Ok(String::new());
    }
    let raw = value.to_string();
    if raw.is_empty() {
        return Ok(String::new());
    }
    let dt = dates::parse(&raw).ok_or_else(|| {
        Error::new(
            ErrorKind::InvalidOperation,
            format!("cannot parse date {raw:?}"),
        )
    })?;
    Ok(dates::php_format(
        dt,
        fmt.as_deref().unwrap_or("Y-m-d H:i:s"),
    ))
}

/// JSON-encode a value for use inside an HTML attribute (Alpine `x-data`).
/// The result is *not* marked safe, so autoescape turns quotes into entities,
/// which the browser decodes again before Alpine evaluates the expression.
fn js_filter(value: Value) -> Result<String, Error> {
    serde_json::to_string(&value)
        .map_err(|e| Error::new(ErrorKind::InvalidOperation, e.to_string()))
}
