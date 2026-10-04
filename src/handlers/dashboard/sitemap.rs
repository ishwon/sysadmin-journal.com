//! Writes `sitemap.xml` plus per-type sitemaps into the public directory.

use axum::response::Response;

use crate::http::{AppError, Ctx};
use crate::models::post::{Order, Post, PostQuery};
use crate::models::tag::Tag;
use crate::models::user::User;
use crate::support::dates;
use crate::support::text::{escape_xml, strip_tags};

/// `POST /dashboard/sitemap/generate`.
pub async fn generate(ctx: Ctx) -> Result<Response, AppError> {
    let db = ctx.db();
    let base = ctx.state.config.app_url.trim_end_matches('/').to_string();
    let public = ctx.state.config.public_dir.clone();
    let now = dates::iso8601(dates::now());

    let mut index = String::from(
        "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n",
    );
    for name in [
        "sitemap-posts.xml",
        "sitemap-pages.xml",
        "sitemap-authors.xml",
        "sitemap-tags.xml",
    ] {
        index.push_str(&format!("  <sitemap>\n    <loc>{base}/{name}</loc>\n    <lastmod>{now}</lastmod>\n  </sitemap>\n"));
    }
    index.push_str("</sitemapindex>");
    std::fs::write(public.join("sitemap.xml"), index)?;

    let posts = PostQuery::new()
        .posts()
        .published()
        .order(Order::PublishedDesc)
        .all(db)
        .await?;
    std::fs::write(
        public.join("sitemap-posts.xml"),
        posts_sitemap(&base, &posts, true),
    )?;

    let pages = PostQuery::new().pages().published().all(db).await?;
    std::fs::write(
        public.join("sitemap-pages.xml"),
        posts_sitemap(&base, &pages, false),
    )?;

    let mut authors = open_url_set();
    for author in User::with_posts(db).await? {
        authors.push_str(&url_entry(
            &format!("{base}/author/{}", author.slug),
            author.updated_at.unwrap_or_else(dates::now),
            author
                .profile_image
                .as_deref()
                .map(|p| resolve_image(&base, p)),
            None,
        ));
    }
    authors.push_str("</urlset>");
    std::fs::write(public.join("sitemap-authors.xml"), authors)?;

    let mut tags = open_url_set();
    for tag in Tag::with_posts(db).await? {
        tags.push_str(&url_entry(
            &format!("{base}/tag/{}", tag.slug),
            tag.updated_at.unwrap_or_else(dates::now),
            tag.feature_image
                .as_deref()
                .map(|p| resolve_image(&base, p)),
            None,
        ));
    }
    tags.push_str("</urlset>");
    std::fs::write(public.join("sitemap-tags.xml"), tags)?;

    ctx.back_with_success("Sitemap generated.").await
}

fn posts_sitemap(base: &str, posts: &[Post], with_captions: bool) -> String {
    let mut xml = open_url_set();
    for post in posts {
        let caption = if with_captions {
            post.feature_image_caption
                .as_deref()
                .filter(|c| !c.is_empty())
                .map(strip_tags)
        } else {
            None
        };
        xml.push_str(&url_entry(
            &format!("{base}/{}", post.slug),
            post.updated_at.unwrap_or_else(dates::now),
            post.feature_image
                .as_deref()
                .filter(|p| !p.is_empty())
                .map(|p| resolve_image(base, p)),
            caption.as_deref(),
        ));
    }
    xml.push_str("</urlset>");
    xml
}

fn open_url_set() -> String {
    "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\">\n".to_string()
}

fn url_entry(
    loc: &str,
    lastmod: chrono::NaiveDateTime,
    image: Option<String>,
    caption: Option<&str>,
) -> String {
    let mut xml = format!(
        "  <url>\n    <loc>{}</loc>\n    <lastmod>{}</lastmod>\n",
        escape_xml(loc),
        dates::iso8601(lastmod)
    );
    if let Some(image) = image.filter(|i| !i.is_empty()) {
        xml.push_str("    <image:image>\n");
        xml.push_str(&format!(
            "      <image:loc>{}</image:loc>\n",
            escape_xml(&image)
        ));
        if let Some(caption) = caption {
            xml.push_str(&format!(
                "      <image:caption>{}</image:caption>\n",
                escape_xml(caption)
            ));
        }
        xml.push_str("    </image:image>\n");
    }
    xml.push_str("  </url>\n");
    xml
}

fn resolve_image(base: &str, path: &str) -> String {
    if path.starts_with("http") {
        path.to_string()
    } else {
        format!("{base}{path}")
    }
}
