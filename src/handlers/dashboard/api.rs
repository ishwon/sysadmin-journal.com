use axum::Json;
use axum::extract::Multipart;
use axum::http::StatusCode;
use axum::response::{IntoResponse, Response};
use serde::Deserialize;
use serde_json::json;

use crate::http::middleware::token_matches;
use crate::http::{AppError, Ctx};
use crate::models::post::Post;
use crate::support::{dates, text};

#[derive(Deserialize, Default)]
pub struct SlugCheck {
    #[serde(default)]
    pub title: String,
    #[serde(default)]
    pub exclude_id: Option<serde_json::Value>,
}

/// `POST /dashboard/api/slug-check`.
pub async fn slug_check(ctx: Ctx, Json(body): Json<SlugCheck>) -> Result<Response, AppError> {
    let exclude_id = body.exclude_id.and_then(|v| match v {
        serde_json::Value::Number(n) => n.as_i64(),
        serde_json::Value::String(s) => s.parse().ok(),
        _ => None,
    });
    let mut slug = text::slug(&body.title);
    if slug.is_empty() {
        return Ok(Json(json!({ "slug": "", "available": true })).into_response());
    }

    let db = ctx.db();
    let mut available = !Post::slug_taken(db, &slug, exclude_id).await?;
    if !available {
        slug = format!("{slug}-{}", dates::php_format(dates::now(), "Ymd"));
        available = !Post::slug_taken(db, &slug, exclude_id).await?;
    }
    Ok(Json(json!({ "slug": slug, "available": available })).into_response())
}

/// `POST /dashboard/api/upload-image` (multipart, field `image`).
pub async fn upload_image(
    ctx: Ctx,
    headers: axum::http::HeaderMap,
    mut multipart: Multipart,
) -> Result<Response, AppError> {
    let mut token = headers
        .get("x-csrf-token")
        .and_then(|v| v.to_str().ok())
        .map(|s| s.to_string());
    let mut image: Option<(String, Vec<u8>)> = None;

    while let Some(field) = multipart
        .next_field()
        .await
        .map_err(|e| anyhow::anyhow!("multipart: {e}"))?
    {
        match field.name().unwrap_or("") {
            "_token" => token = field.text().await.ok(),
            "image" => {
                let name = field.file_name().unwrap_or("").to_string();
                let content_type = field.content_type().unwrap_or("").to_string();
                let bytes = field
                    .bytes()
                    .await
                    .map_err(|e| anyhow::anyhow!("multipart: {e}"))?;
                let ext = std::path::Path::new(&name)
                    .extension()
                    .and_then(|e| e.to_str())
                    .map(|e| e.to_ascii_lowercase());
                let ext = ext
                    .filter(|e| {
                        ["jpeg", "jpg", "png", "gif", "webp", "svg", "avif", "bmp"]
                            .contains(&e.as_str())
                    })
                    .or_else(|| {
                        mime_guess::get_mime_extensions_str(&content_type)
                            .and_then(|e| e.first())
                            .map(|e| e.to_string())
                    });
                if let Some(ext) = ext {
                    image = Some((ext, bytes.to_vec()));
                }
            }
            _ => {}
        }
    }

    if !token_matches(Some(&ctx.csrf), token.as_deref()) {
        return Ok((
            StatusCode::from_u16(419).unwrap_or_default(),
            Json(json!({ "message": "CSRF token mismatch." })),
        )
            .into_response());
    }
    let Some((ext, bytes)) = image else {
        return Ok((
            StatusCode::UNPROCESSABLE_ENTITY,
            Json(json!({ "message": "The image field must be an image." })),
        )
            .into_response());
    };
    if bytes.len() > 10 * 1024 * 1024 {
        return Ok((
            StatusCode::UNPROCESSABLE_ENTITY,
            Json(json!({ "message": "The image field must not be greater than 10240 kilobytes." })),
        )
            .into_response());
    }

    let folder = dates::php_format(dates::now(), "Y/m");
    let dir = ctx.state.config.media_dir.join("images").join(&folder);
    std::fs::create_dir_all(&dir)?;
    let file_name = format!("{}.{ext}", text::random(40));
    std::fs::write(dir.join(&file_name), &bytes)?;

    Ok(Json(json!({ "path": format!("/content/images/{folder}/{file_name}") })).into_response())
}
