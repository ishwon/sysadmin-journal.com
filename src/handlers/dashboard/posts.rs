use axum::extract::Path;
use axum::response::{Html, IntoResponse, Response};
use chrono::NaiveDateTime;

use super::{PER_PAGE, process_content};
use crate::http::form::FormData;
use crate::http::validation::Validator;
use crate::http::{AppError, Ctx};
use crate::models::gallery::Gallery;
use crate::models::post::{Order, Post, PostInput, PostQuery, PostView};
use crate::models::tag::Tag;
use crate::support::dates;
use crate::support::pagination::Page;
use crate::support::text;
use crate::views::layouts::DashboardPage;
use crate::views::pages::{PostForm, PostPreview, PostsAdmin};
use crate::views::{render, ui};

/// `GET /dashboard/posts` with the all / published / drafts filter.
pub async fn index(ctx: Ctx) -> Result<Response, AppError> {
    let db = ctx.db();
    let status = ctx
        .query_value("status")
        .filter(|s| *s == "published" || *s == "draft")
        .map(|s| s.to_string());
    let mut query = PostQuery::new().posts().order(Order::UpdatedDesc);
    if let Some(s) = &status {
        query = query.status(s);
    }
    let page = ctx.page_number();
    let (posts, total) = query.paginate(db, PER_PAGE, page).await?;
    let posts = Page::new(
        Post::load_relations(db, posts).await?,
        total,
        PER_PAGE,
        page,
        &ctx.path,
        &ctx.query,
    );

    let active = "text-ink-900 border-b border-ink-900";
    let inactive = "text-ink-400";
    let filter = |label: &str, href: &str, on: bool| {
        format!(
            "<a href=\"{href}\" class=\"{} pb-0.5\">{label}</a>",
            if on { active } else { inactive }
        )
    };
    let aside = format!(
        "<div><div class=\"flex gap-4 meta\">{}{}{}</div></div>",
        filter("all", "/dashboard/posts", status.is_none()),
        filter(
            "published",
            "/dashboard/posts?status=published",
            status.as_deref() == Some("published")
        ),
        filter(
            "drafts",
            "/dashboard/posts?status=draft",
            status.as_deref() == Some("draft")
        )
    );

    let base = ctx.base();
    let content = render(PostsAdmin { base: &base, posts })?;
    let page = DashboardPage::new("Posts", content)
        .heading("Posts")
        .aside(aside)
        .actions(
            ui::button("primary")
                .size("sm")
                .href("/dashboard/posts/create")
                .html("New post"),
        );
    ctx.dashboard(&base, page).await
}

async fn form_page(
    ctx: &Ctx,
    title: &str,
    post: Option<PostView>,
    post_tag_ids: Vec<i64>,
) -> Result<Response, AppError> {
    let db = ctx.db();
    let tags = Tag::all_by_name(db).await?;
    let galleries = Gallery::all_by_title(db).await?;
    let (action, editing) = match &post {
        Some(p) => (format!("/dashboard/posts/{}", p.id), true),
        None => ("/dashboard/posts".to_string(), false),
    };
    let base = ctx.base();
    let content = render(PostForm {
        base: &base,
        post,
        post_tag_ids,
        tags,
        galleries,
        action,
        editing,
    })?;
    ctx.dashboard(&base, DashboardPage::new(title, content))
        .await
}

/// `GET /dashboard/posts/create`.
pub async fn create(ctx: Ctx) -> Result<Response, AppError> {
    form_page(&ctx, "New Post", None, Vec::new()).await
}

/// `GET /dashboard/posts/{id}/edit`.
pub async fn edit(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let db = ctx.db();
    let post = Post::find(db, id).await?.ok_or(AppError::NotFound)?;
    let post_tag_ids = Post::tag_ids(db, post.id).await?;
    let post = post.into_view(db).await?;
    form_page(&ctx, "Edit Post", Some(post), post_tag_ids).await
}

/// `GET /dashboard/posts/{id}` — preview with the gallery shortcodes rendered.
pub async fn show(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let db = ctx.db();
    let post = Post::find(db, id).await?.ok_or(AppError::NotFound)?;
    let mut view = post.into_view(db).await?;
    view.post.html = super::super::render_gallery_shortcodes(db, view.post.html.take()).await?;
    let base = ctx.base();
    Ok(Html(render(PostPreview {
        base: &base,
        post: view,
    })?)
    .into_response())
}

fn validate(form: &FormData) -> Validator {
    let mut v = Validator::new();
    v.required(form, "title")
        .max(form, "title", 255)
        .required(form, "slug")
        .max(form, "slug", 255)
        .one_of(form, "content_format", &["html", "markdown"])
        .one_of(form, "status", &["published", "draft"])
        .date(form, "published_at")
        .max(form, "meta_title", 255);
    v
}

async fn build_input(
    ctx: &Ctx,
    form: &FormData,
    existing: Option<&Post>,
) -> Result<PostInput, AppError> {
    let format = form.str("content_format");
    let content = form.opt("content");
    let html = process_content(content.as_deref(), &format);
    let plaintext = text::strip_tags(html.as_deref().unwrap_or(""));
    let status = form.str("status");

    let requested_slug = text::slug(&form.str("slug"));
    let slug = match existing {
        Some(post) if requested_slug == post.slug => post.slug.clone(),
        Some(post) => resolve_slug(ctx, &requested_slug, Some(post.id)).await?,
        None => resolve_slug(ctx, &requested_slug, None).await?,
    };

    let published_at = resolve_published_at(
        form.opt("published_at").as_deref(),
        &status,
        existing.and_then(|p| p.published_at),
    );

    Ok(PostInput {
        title: form.str("title"),
        slug,
        reading_time: text::reading_time(Some(&plaintext)),
        html,
        plaintext: Some(plaintext),
        markdown: if format == "markdown" { content } else { None },
        custom_excerpt: form.opt("custom_excerpt"),
        feature_image: form.opt("feature_image"),
        feature_image_alt: form.opt("feature_image_alt"),
        feature_image_caption: form.opt("feature_image_caption"),
        kind: "post".into(),
        status,
        published_at,
        meta_title: form.opt("meta_title"),
        meta_description: form.opt("meta_description"),
        og_image: form.opt("og_image"),
        twitter_image: form.opt("twitter_image"),
        gallery_id: form.opt("gallery_id").and_then(|g| g.parse().ok()),
        ..Default::default()
    })
}

fn resolve_published_at(
    input: Option<&str>,
    status: &str,
    existing: Option<NaiveDateTime>,
) -> Option<NaiveDateTime> {
    if let Some(dt) = input
        .filter(|v| !v.trim().is_empty())
        .and_then(dates::parse)
    {
        return Some(dt);
    }
    if status == "published" && existing.is_none() {
        return Some(dates::now());
    }
    existing
}

/// Append `-YYYYMMDD` (then `-2`, `-3`, …) when the slug is already taken.
async fn resolve_slug(ctx: &Ctx, slug: &str, exclude_id: Option<i64>) -> Result<String, AppError> {
    let db = ctx.db();
    let mut slug = slug.to_string();
    if Post::slug_taken(db, &slug, exclude_id).await? {
        slug = format!("{slug}-{}", dates::php_format(dates::now(), "Ymd"));
        let base = slug.clone();
        let mut counter = 2;
        while Post::slug_taken(db, &slug, exclude_id).await? {
            slug = format!("{base}-{counter}");
            counter += 1;
        }
    }
    Ok(slug)
}

fn tag_ids(form: &FormData) -> Vec<i64> {
    form.list("tags")
        .iter()
        .filter_map(|t| t.parse::<i64>().ok())
        .collect()
}

async fn gallery_exists(ctx: &Ctx, form: &FormData, v: &mut Validator) -> Result<(), AppError> {
    if let Some(id) = form.opt("gallery_id") {
        let ok = match id.parse::<i64>() {
            Ok(id) => Gallery::exists(ctx.db(), id).await?,
            Err(_) => false,
        };
        if !ok {
            v.fail("The selected gallery id is invalid.");
        }
    }
    Ok(())
}

/// `POST /dashboard/posts`.
pub async fn store(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let mut v = validate(&form);
    gallery_exists(&ctx, &form, &mut v).await?;
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }

    let input = build_input(&ctx, &form, None).await?;
    let db = ctx.db();
    let id = Post::create(db, &input).await?;

    let tags = tag_ids(&form);
    if !tags.is_empty() {
        Post::sync_tags(db, id, &tags).await?;
    }
    Post::attach_author(db, id, ctx.auth()?.id, 0).await?;

    ctx.redirect_with_success("/dashboard/posts", "Post created.")
        .await
}

/// `PUT /dashboard/posts/{id}`.
pub async fn update(ctx: Ctx, Path(id): Path<i64>, form: FormData) -> Result<Response, AppError> {
    let db = ctx.db();
    let post = Post::find(db, id).await?.ok_or(AppError::NotFound)?;

    let mut v = validate(&form);
    gallery_exists(&ctx, &form, &mut v).await?;
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }

    let input = build_input(&ctx, &form, Some(&post)).await?;
    Post::update(db, post.id, &input).await?;
    Post::sync_tags(db, post.id, &tag_ids(&form)).await?;

    ctx.redirect_with_success(
        &format!("/dashboard/posts/{}/edit", post.id),
        "Article updated.",
    )
    .await
}

/// `DELETE /dashboard/posts/{id}`.
pub async fn destroy(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let db = ctx.db();
    let post = Post::find(db, id).await?.ok_or(AppError::NotFound)?;
    Post::delete(db, post.id).await?;
    ctx.redirect_with_success("/dashboard/posts", "Post deleted.")
        .await
}
