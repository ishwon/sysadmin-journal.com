use axum::Json;
use axum::response::{IntoResponse, Response};
use serde::Serialize;

use crate::http::{AppError, Ctx};
use crate::models::post::{Order, Post, PostQuery};
use crate::support::{dates, text};

#[derive(Serialize)]
struct Hit {
    title: String,
    slug: String,
    excerpt: String,
    tag: Option<String>,
    date: Option<String>,
}

/// `GET /search?q=` — up to ten published posts matching the title or body.
pub async fn search(ctx: Ctx) -> Result<Response, AppError> {
    let query = ctx.query_value("q").unwrap_or("").to_string();
    if query.chars().count() < 2 {
        return Ok(Json(Vec::<Hit>::new()).into_response());
    }

    let db = ctx.db();
    let posts = PostQuery::new()
        .posts()
        .published()
        .search(&query)
        .order(Order::PublishedDesc)
        .limit(db, 10)
        .await?;
    let hits: Vec<Hit> = Post::load_relations(db, posts)
        .await?
        .into_iter()
        .map(|view| Hit {
            title: view.post.title.clone(),
            slug: view.post.slug.clone(),
            excerpt: text::limit(&view.plain_excerpt, 100),
            tag: view.primary_tag.as_ref().map(|t| t.name.clone()),
            date: view
                .post
                .published_at
                .map(|d| dates::php_format(d, "d F Y")),
        })
        .collect();

    Ok(Json(hits).into_response())
}
