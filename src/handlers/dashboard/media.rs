//! The media browser: folders and files under `MEDIA_DIR/images`, served
//! publicly at `/content/images/...`.

use std::path::{Path as FsPath, PathBuf};
use std::sync::LazyLock;

use axum::extract::Multipart;
use axum::response::Response;
use regex::Regex;
use serde::Serialize;

use crate::http::form::FormData;
use crate::http::middleware::token_matches;
use crate::http::validation::Validator;
use crate::http::{AppError, Ctx};
use crate::views::layouts::DashboardPage;
use crate::views::pages::MediaIndex;
use crate::views::{render, ui};

const BASE: &str = "images";
const MAX_FILE_BYTES: usize = 10 * 1024 * 1024;
const MAX_FILES: usize = 20;
const ALLOWED_EXTENSIONS: &[&str] = &["jpeg", "jpg", "png", "gif", "webp", "svg", "avif", "pdf"];

static DIR_NAME_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r"^[a-zA-Z0-9_-]+$").expect("valid regex"));

#[derive(Clone, Debug, Serialize)]
pub struct MediaFile {
    pub name: String,
    pub path: String,
    pub url: String,
    pub size: u64,
    pub modified: i64,
    #[serde(rename = "type")]
    pub kind: &'static str,
}

#[derive(Clone, Debug, Serialize)]
pub struct Crumb {
    pub name: String,
    pub path: String,
}

/// Reject traversal: keep only plain path segments.
fn sanitize_path(path: Option<&str>) -> String {
    path.unwrap_or("")
        .replace('\\', "/")
        .split('/')
        .filter(|s| !s.is_empty() && *s != "." && *s != "..")
        .collect::<Vec<_>>()
        .join("/")
}

fn base_name(value: &str) -> String {
    value
        .replace('\\', "/")
        .rsplit('/')
        .next()
        .unwrap_or("")
        .to_string()
}

fn relative_dir(current: &str) -> String {
    if current.is_empty() {
        BASE.to_string()
    } else {
        format!("{BASE}/{current}")
    }
}

fn full_dir(ctx: &Ctx, current: &str) -> PathBuf {
    ctx.state.config.media_dir.join(relative_dir(current))
}

fn media_index_url(current: &str) -> String {
    if current.is_empty() {
        "/dashboard/media".to_string()
    } else {
        let encoded: String = form_urlencoded::byte_serialize(current.as_bytes()).collect();
        format!("/dashboard/media?path={encoded}")
    }
}

fn extension_of(name: &str) -> String {
    FsPath::new(name)
        .extension()
        .and_then(|e| e.to_str())
        .unwrap_or("")
        .to_ascii_lowercase()
}

/// `GET /dashboard/media`.
pub async fn index(ctx: Ctx) -> Result<Response, AppError> {
    let current = sanitize_path(ctx.query_value("path"));
    let dir = full_dir(&ctx, &current);
    if current.is_empty() {
        // The media root is not checked in; create it on first visit.
        std::fs::create_dir_all(&dir)?;
    }
    if !dir.is_dir() {
        return Err(AppError::NotFound);
    }

    let mut directories: Vec<String> = Vec::new();
    let mut files: Vec<MediaFile> = Vec::new();
    for entry in std::fs::read_dir(&dir)? {
        let entry = entry?;
        let name = entry.file_name().to_string_lossy().to_string();
        let meta = entry.metadata()?;
        if meta.is_dir() {
            directories.push(name);
        } else if meta.is_file() {
            let ext = extension_of(&name);
            if !ALLOWED_EXTENSIONS.contains(&ext.as_str()) {
                continue;
            }
            let rel = format!("{}/{name}", relative_dir(&current));
            let modified = meta
                .modified()
                .ok()
                .and_then(|m| m.duration_since(std::time::UNIX_EPOCH).ok())
                .map(|d| d.as_secs() as i64)
                .unwrap_or(0);
            files.push(MediaFile {
                url: format!("/content/{rel}"),
                path: rel,
                name,
                size: meta.len(),
                modified,
                kind: if ext == "pdf" { "pdf" } else { "image" },
            });
        }
    }
    directories.sort();
    files.sort_by(|a, b| {
        b.modified
            .cmp(&a.modified)
            .then_with(|| a.name.cmp(&b.name))
    });

    let images: Vec<MediaFile> = files
        .iter()
        .filter(|f| f.kind == "image")
        .cloned()
        .collect();
    let pdfs: Vec<MediaFile> = files.iter().filter(|f| f.kind == "pdf").cloned().collect();

    let mut breadcrumbs = vec![Crumb {
        name: BASE.into(),
        path: String::new(),
    }];
    let mut accumulated = String::new();
    for segment in current.split('/').filter(|s| !s.is_empty()) {
        accumulated = if accumulated.is_empty() {
            segment.to_string()
        } else {
            format!("{accumulated}/{segment}")
        };
        breadcrumbs.push(Crumb {
            name: segment.to_string(),
            path: accumulated.clone(),
        });
    }
    let mut crumbs = vec![ui::crumb("dashboard", Some("/dashboard"))];
    crumbs.extend(
        breadcrumbs
            .iter()
            .map(|c| ui::crumb(&c.name.to_lowercase(), Some(&media_index_url(&c.path)))),
    );

    let file_count = images.len() + pdfs.len();
    let base = ctx.base();
    let content = render(MediaIndex {
        base: &base,
        current_media_path: current,
        directories,
        images,
        pdfs,
    })?;
    let page = DashboardPage::new("Media", content)
        .heading("Media")
        .breadcrumbs(ui::breadcrumbs(&crumbs))
        .aside(format!(
            "<div><p class=\"meta\">{file_count} files</p></div>"
        ))
        .actions(format!(
            "{}{}",
            ui::button("secondary")
                .size("sm")
                .attrs(r#"x-data @click="$dispatch('open-modal-create-dir')""#)
                .html("New folder"),
            ui::button("primary")
                .size("sm")
                .attrs(r#"x-data @click="$dispatch('open-modal-upload')""#)
                .html("Upload")
        ));
    ctx.dashboard(&base, page).await
}

/// `POST /dashboard/media/directory`.
pub async fn create_directory(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let mut v = Validator::new();
    v.required(&form, "name")
        .max(&form, "name", 255)
        .regex(&form, "name", &DIR_NAME_RE);
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    let current = sanitize_path(form.get("current_path"));
    let target = full_dir(&ctx, &current).join(form.str("name"));
    if target.is_dir() {
        return ctx
            .back_with_errors(vec!["Directory already exists.".into()], None)
            .await;
    }
    std::fs::create_dir_all(&target)?;
    ctx.redirect_with_success(&media_index_url(&current), "Directory created.")
        .await
}

/// `DELETE /dashboard/media/directory`.
pub async fn delete_directory(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let mut v = Validator::new();
    v.required(&form, "directory");
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    let current = sanitize_path(form.get("current_path"));
    let name = base_name(&form.str("directory"));
    let target = full_dir(&ctx, &current).join(&name);
    if name.is_empty() || name == "." || name == ".." || !target.is_dir() {
        return ctx
            .back_with_errors(vec!["Directory not found.".into()], None)
            .await;
    }
    if std::fs::read_dir(&target)?.next().is_some() {
        return ctx
            .back_with_errors(vec!["Directory is not empty.".into()], None)
            .await;
    }
    std::fs::remove_dir(&target)?;
    ctx.redirect_with_success(&media_index_url(&current), "Directory deleted.")
        .await
}

struct Upload {
    name: String,
    bytes: Vec<u8>,
}

/// `POST /dashboard/media/upload` (multipart).
pub async fn upload(ctx: Ctx, mut multipart: Multipart) -> Result<Response, AppError> {
    let mut token = None;
    let mut current_path = None;
    let mut uploads: Vec<Upload> = Vec::new();
    let mut errors: Vec<String> = Vec::new();

    while let Some(field) = multipart
        .next_field()
        .await
        .map_err(|e| anyhow::anyhow!("multipart: {e}"))?
    {
        let name = field.name().unwrap_or("").to_string();
        match name.as_str() {
            "_token" => token = field.text().await.ok(),
            "current_path" => current_path = field.text().await.ok(),
            "photos[]" | "photos" => {
                let file_name = base_name(field.file_name().unwrap_or(""));
                let bytes = field
                    .bytes()
                    .await
                    .map_err(|e| anyhow::anyhow!("multipart: {e}"))?;
                if file_name.is_empty() {
                    continue;
                }
                let ext = extension_of(&file_name);
                if !ALLOWED_EXTENSIONS.contains(&ext.as_str()) {
                    errors.push(format!("The file {file_name} must be a file of type: jpeg, jpg, png, gif, webp, svg, avif, pdf."));
                } else if bytes.len() > MAX_FILE_BYTES {
                    errors.push(format!(
                        "The file {file_name} must not be greater than 10240 kilobytes."
                    ));
                } else {
                    uploads.push(Upload {
                        name: file_name,
                        bytes: bytes.to_vec(),
                    });
                }
            }
            _ => {}
        }
    }

    if !token_matches(Some(&ctx.csrf), token.as_deref()) {
        return Ok(crate::http::error::error_page(
            axum::http::StatusCode::from_u16(419).unwrap_or_default(),
            "Page Expired",
        ));
    }
    if uploads.is_empty() && errors.is_empty() {
        errors.push("The photos field is required.".into());
    }
    if uploads.len() > MAX_FILES {
        errors.push(format!(
            "The photos field must not have more than {MAX_FILES} items."
        ));
    }
    if !errors.is_empty() {
        return ctx.back_with_errors(errors, None).await;
    }

    let current = sanitize_path(current_path.as_deref());
    let dir = full_dir(&ctx, &current);
    std::fs::create_dir_all(&dir)?;
    let count = uploads.len();
    for upload in uploads {
        std::fs::write(dir.join(&upload.name), &upload.bytes)?;
    }

    let message = format!(
        "{count} {} uploaded.",
        crate::support::text::plural("file", count as i64)
    );
    ctx.redirect_with_success(&media_index_url(&current), &message)
        .await
}

/// `DELETE /dashboard/media/photo`.
pub async fn delete_photo(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let mut v = Validator::new();
    v.required(&form, "file");
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    let current = sanitize_path(form.get("current_path"));
    let name = base_name(&form.str("file"));
    let target = full_dir(&ctx, &current).join(&name);
    if name.is_empty() || !target.is_file() {
        return ctx
            .back_with_errors(vec!["File not found.".into()], None)
            .await;
    }
    std::fs::remove_file(&target)?;
    ctx.redirect_with_success(&media_index_url(&current), "Photo deleted.")
        .await
}
