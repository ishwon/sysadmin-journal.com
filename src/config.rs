use std::env;
use std::path::PathBuf;

use anyhow::{Context, Result};
use base64::Engine;

/// Runtime configuration, read from the environment (and `.env`).
#[derive(Clone, Debug)]
pub struct Config {
    pub app_name: String,
    pub app_env: String,
    pub app_url: String,
    pub host: String,
    pub port: u16,
    pub database_url: String,
    pub session_lifetime_minutes: i64,
    pub bcrypt_rounds: u32,
    pub public_dir: PathBuf,
    pub media_dir: PathBuf,
    pub templates_dir: PathBuf,
    /// Raw key material used to sign cookies (remember-me).
    pub key: Vec<u8>,
}

impl Config {
    pub fn from_env() -> Result<Self> {
        let app_env = var_or("APP_ENV", "production");
        let key = parse_key(env::var("APP_KEY").ok(), &app_env)?;

        Ok(Self {
            app_name: var_or("APP_NAME", "SysAdmin Journal"),
            app_url: var_or("APP_URL", "http://localhost:8000")
                .trim_end_matches('/')
                .to_string(),
            host: var_or("APP_HOST", "127.0.0.1"),
            port: var_or("APP_PORT", "8000")
                .parse()
                .context("APP_PORT must be a number")?,
            database_url: var_or("DATABASE_URL", "sqlite:database/database.sqlite"),
            session_lifetime_minutes: var_or("SESSION_LIFETIME", "120")
                .parse()
                .context("SESSION_LIFETIME must be a number")?,
            bcrypt_rounds: var_or("BCRYPT_ROUNDS", "12")
                .parse()
                .context("BCRYPT_ROUNDS must be a number")?,
            public_dir: PathBuf::from(var_or("PUBLIC_DIR", "public")),
            media_dir: PathBuf::from(var_or("MEDIA_DIR", "storage/app/public")),
            templates_dir: PathBuf::from(var_or("TEMPLATES_DIR", "templates")),
            app_env,
            key,
        })
    }

    pub fn is_local(&self) -> bool {
        self.app_env == "local"
    }

    /// Absolute URL for a site path, mirroring Laravel's `url($path)`.
    pub fn url(&self, path: &str) -> String {
        if path.starts_with("http://") || path.starts_with("https://") {
            return path.to_string();
        }
        format!("{}/{}", self.app_url, path.trim_start_matches('/'))
    }

    /// Host portion of APP_URL, used as the slug prefix in the editor.
    pub fn host_name(&self) -> String {
        self.app_url
            .trim_start_matches("http://")
            .trim_start_matches("https://")
            .split('/')
            .next()
            .unwrap_or("sysadmin-journal.com")
            .to_string()
    }
}

fn var_or(name: &str, default: &str) -> String {
    env::var(name)
        .ok()
        .filter(|v| !v.is_empty())
        .unwrap_or_else(|| default.to_string())
}

fn parse_key(raw: Option<String>, app_env: &str) -> Result<Vec<u8>> {
    let raw = raw.unwrap_or_default();
    if raw.is_empty() {
        if app_env == "production" {
            anyhow::bail!("APP_KEY is not set. Generate one with `openssl rand -base64 32`.");
        }
        tracing::warn!("APP_KEY is empty; using an insecure development key");
        return Ok(vec![7u8; 64]);
    }
    let encoded = raw.strip_prefix("base64:").unwrap_or(&raw);
    let bytes = base64::engine::general_purpose::STANDARD
        .decode(encoded)
        .unwrap_or_else(|_| raw.as_bytes().to_vec());
    if bytes.len() < 32 {
        anyhow::bail!("APP_KEY must decode to at least 32 bytes");
    }
    Ok(bytes)
}
