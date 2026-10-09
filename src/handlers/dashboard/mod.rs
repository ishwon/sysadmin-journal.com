pub mod api;
pub mod galleries;
pub mod media;
pub mod pages;
pub mod posts;
pub mod sitemap;
pub mod tags;
pub mod users;

use axum::response::Response;

use crate::http::{AppError, Ctx};
use crate::models::gallery::Gallery;
use crate::models::post::{Order, Post, PostQuery};
use crate::models::tag::Tag;
use crate::models::user::User;
use crate::support::dates;
use crate::views::layouts::DashboardPage;
use crate::views::pages::DashboardIndex;
use crate::views::render;

pub const PER_PAGE: i64 = 20;

/// `GET /dashboard`.
pub async fn index(ctx: Ctx) -> Result<Response, AppError> {
    let db = ctx.db();
    let recent = PostQuery::new()
        .posts()
        .order(Order::UpdatedDesc)
        .limit(db, 6)
        .await?;
    let recent_posts = Post::load_relations(db, recent).await?;
    let sitemap_exists = ctx.state.config.public_dir.join("sitemap.xml").is_file();

    let hour: u32 = dates::now().format("%H").to_string().parse().unwrap_or(12);
    let greeting = if hour < 12 {
        "Good morning"
    } else if hour < 18 {
        "Good afternoon"
    } else {
        "Good evening"
    };
    let first_name = ctx
        .user
        .as_ref()
        .map(|u| u.name.split(' ').next().unwrap_or("there").to_string())
        .unwrap_or_else(|| "there".into());

    let base = ctx.base();
    let content = render(DashboardIndex {
        base: &base,
        post_count: PostQuery::new().posts().count(db).await?,
        published_count: PostQuery::new().posts().published().count(db).await?,
        page_count: PostQuery::new().pages().count(db).await?,
        gallery_count: Gallery::count(db).await?,
        tag_count: Tag::count(db).await?,
        user_count: User::count(db).await?,
        sitemap_exists,
        recent_posts,
    })?;
    let page = DashboardPage::new("Dashboard", content)
        .heading(format!("{greeting}, {first_name}."))
        .aside(format!(
            "<div><p class=\"meta\">{}</p></div>",
            dates::php_format(dates::now(), "l, j F Y")
        ));
    ctx.dashboard(&base, page).await
}

/// Convert editor content to HTML according to the chosen format.
pub fn process_content(content: Option<&str>, format: &str) -> Option<String> {
    let content = content.filter(|c| !c.is_empty())?;
    if format == "markdown" {
        crate::support::markdown::convert(Some(content))
    } else {
        Some(content.to_string())
    }
}
