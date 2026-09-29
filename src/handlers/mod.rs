pub mod auth;
pub mod dashboard;
pub mod feed;
pub mod public;
pub mod search;

use std::sync::LazyLock;

use regex::Regex;

use crate::db::Db;
use crate::models::gallery::Gallery;
use crate::support::text;

static GALLERY_SHORTCODE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r"\[gallery:([a-z0-9-]+)\]").expect("valid regex"));

/// Replace `[gallery:slug]` shortcodes with an image grid.
pub async fn render_gallery_shortcodes(
    db: &Db,
    html: Option<String>,
) -> sqlx::Result<Option<String>> {
    let Some(html) = html else { return Ok(None) };
    if html.is_empty() {
        return Ok(Some(html));
    }

    let slugs: Vec<String> = GALLERY_SHORTCODE
        .captures_iter(&html)
        .map(|c| c[1].to_string())
        .collect();
    if slugs.is_empty() {
        return Ok(Some(html));
    }

    let mut rendered: std::collections::HashMap<String, String> = std::collections::HashMap::new();
    for slug in slugs {
        if rendered.contains_key(&slug) {
            continue;
        }
        let markup = match Gallery::find_by_slug(db, &slug).await? {
            Some(gallery) => {
                let images = Gallery::images(db, gallery.id).await?;
                let items: Vec<String> = images
                    .iter()
                    .map(|image| {
                        let alt = text::escape(image.alt_text.as_deref().or(image.caption.as_deref()).unwrap_or(""));
                        format!(
                            "<div><img src=\"{}\" alt=\"{alt}\" class=\"w-full h-48 object-cover rounded-lg\"></div>",
                            image.image_path
                        )
                    })
                    .collect();
                format!(
                    "<div class=\"grid grid-cols-2 md:grid-cols-3 gap-4 my-8\">{}</div>",
                    items.join("\n")
                )
            }
            None => String::new(),
        };
        rendered.insert(slug, markup);
    }

    Ok(Some(
        GALLERY_SHORTCODE
            .replace_all(&html, |caps: &regex::Captures| {
                rendered.get(&caps[1]).cloned().unwrap_or_default()
            })
            .into_owned(),
    ))
}

/// Strip gallery shortcodes (feeds).
pub fn strip_gallery_shortcodes(html: &str) -> String {
    GALLERY_SHORTCODE.replace_all(html, "").into_owned()
}
