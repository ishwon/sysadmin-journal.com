use std::sync::LazyLock;

use axum::extract::Path;
use axum::http::header;
use axum::response::{IntoResponse, Response};
use regex::Regex;

use crate::http::{AppError, Ctx};
use crate::models::post::{Order, Post, PostQuery};
use crate::models::tag::Tag;
use crate::support::dates;
use crate::views::pages::Rss;
use crate::views::render;

const ITEM_LIMIT: i64 = 20;

/// `src="/x"` / `href="/x"` but not protocol-relative `//`.
static ROOT_RELATIVE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r#"\b(src|href)="/([^/])"#).expect("valid regex"));

/// `GET /rss`.
pub async fn index(ctx: Ctx) -> Result<Response, AppError> {
    let config = ctx.state.config.clone();
    feed_response(
        &ctx,
        PostQuery::new().posts(),
        "SysAdmin Journal".to_string(),
        "Thoughts, ideas and stories".to_string(),
        config.url("/"),
        config.url("/rss"),
    )
    .await
}

/// `GET /tag/{slug}/rss`.
pub async fn tag(ctx: Ctx, Path(slug): Path<String>) -> Result<Response, AppError> {
    let tag = Tag::find_by_slug(ctx.db(), &slug)
        .await?
        .ok_or(AppError::NotFound)?;
    let config = ctx.state.config.clone();
    feed_response(
        &ctx,
        PostQuery::new().posts().tag(tag.id),
        format!("SysAdmin Journal · {}", tag.name),
        tag.meta_description
            .clone()
            .filter(|s| !s.is_empty())
            .unwrap_or_else(|| format!("Posts tagged with {}", tag.name)),
        config.url(&format!("/tag/{}", tag.slug)),
        config.url(&format!("/tag/{}/rss", tag.slug)),
    )
    .await
}

async fn feed_response(
    ctx: &Ctx,
    query: PostQuery,
    title: String,
    description: String,
    link: String,
    self_url: String,
) -> Result<Response, AppError> {
    let db = ctx.db();
    let posts = query
        .published()
        .order(Order::PublishedDesc)
        .limit(db, ITEM_LIMIT)
        .await?;
    let mut posts = Post::load_relations(db, posts).await?;

    let base_url = ctx.state.config.url("/").trim_end_matches('/').to_string();
    for view in &mut posts {
        view.post.html = Some(prepare_content(view.post.html.as_deref(), &base_url));
        view.post.feature_image = view
            .post
            .feature_image
            .as_deref()
            .map(|p| absolute(&base_url, p));
    }

    let last_build_date = posts
        .first()
        .and_then(|p| p.published_at)
        .unwrap_or_else(dates::now);

    let base = ctx.base();
    let xml = render(Rss {
        base: &base,
        title,
        description,
        link,
        self_url,
        last_build_date: dates::rss(last_build_date),
        posts,
    })?;

    Ok((
        [(header::CONTENT_TYPE, "application/xml; charset=UTF-8")],
        xml,
    )
        .into_response())
}

/// Make post HTML safe for feed readers: drop gallery shortcodes, make
/// root-relative links and images absolute, and guard the CDATA wrapper.
fn prepare_content(html: Option<&str>, base: &str) -> String {
    let html = super::strip_gallery_shortcodes(html.unwrap_or(""));
    let html = ROOT_RELATIVE.replace_all(&html, |caps: &regex::Captures| {
        format!("{}=\"{base}/{}", &caps[1], &caps[2])
    });
    html.replace("]]>", "]]]]><![CDATA[>")
}

fn absolute(base: &str, path: &str) -> String {
    if path.is_empty() || path.starts_with("http") {
        return path.to_string();
    }
    format!("{base}/{}", path.trim_start_matches('/'))
}
