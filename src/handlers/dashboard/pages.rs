use axum::extract::Path;
use axum::response::Response;

use super::{PER_PAGE, process_content};
use crate::http::form::FormData;
use crate::http::validation::Validator;
use crate::http::{AppError, Ctx};
use crate::models::post::{Order, Post, PostInput, PostQuery};
use crate::support::dates;
use crate::support::pagination::Page;
use crate::support::text;
use crate::views::layouts::DashboardPage;
use crate::views::pages::{PageForm, PagesAdmin};
use crate::views::{render, ui};

/// `GET /dashboard/pages`.
pub async fn index(ctx: Ctx) -> Result<Response, AppError> {
    let db = ctx.db();
    let page = ctx.page_number();
    let (pages, total) = PostQuery::new()
        .pages()
        .order(Order::UpdatedDesc)
        .paginate(db, PER_PAGE, page)
        .await?;
    let pages = Page::new(pages, total, PER_PAGE, page, &ctx.path, &ctx.query);
    let base = ctx.base();
    let content = render(PagesAdmin { base: &base, pages })?;
    let page = DashboardPage::new("Pages", content)
        .heading("Pages")
        .actions(
            ui::button("primary")
                .size("sm")
                .href("/dashboard/pages/create")
                .html("New page"),
        );
    ctx.dashboard(&base, page).await
}

async fn form_page(ctx: &Ctx, title: &str, page: Option<Post>) -> Result<Response, AppError> {
    let (action, editing) = match &page {
        Some(p) => (format!("/dashboard/pages/{}", p.id), true),
        None => ("/dashboard/pages".to_string(), false),
    };
    let base = ctx.base();
    let content = render(PageForm {
        base: &base,
        page,
        action,
        editing,
    })?;
    ctx.dashboard(&base, DashboardPage::new(title, content))
        .await
}

/// `GET /dashboard/pages/create`.
pub async fn create(ctx: Ctx) -> Result<Response, AppError> {
    form_page(&ctx, "New Page", None).await
}

/// `GET /dashboard/pages/{id}/edit`.
pub async fn edit(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let page = Post::find(ctx.db(), id).await?.ok_or(AppError::NotFound)?;
    form_page(&ctx, "Edit Page", Some(page)).await
}

fn validate(form: &FormData) -> Validator {
    let mut v = Validator::new();
    v.required(form, "title")
        .max(form, "title", 255)
        .required(form, "slug")
        .max(form, "slug", 255)
        .one_of(form, "content_format", &["html", "markdown"])
        .one_of(form, "status", &["published", "draft"])
        .max(form, "meta_title", 255);
    v
}

async fn resolve_slug(ctx: &Ctx, slug: &str, exclude_id: Option<i64>) -> Result<String, AppError> {
    if Post::slug_taken(ctx.db(), slug, exclude_id).await? {
        return Ok(format!("{slug}-{}", dates::php_format(dates::now(), "Ymd")));
    }
    Ok(slug.to_string())
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

    let requested = form.str("slug");
    let slug = match existing {
        Some(page) if requested == page.slug => page.slug.clone(),
        Some(page) => resolve_slug(ctx, &text::slug(&requested), Some(page.id)).await?,
        None => resolve_slug(ctx, &text::slug(&requested), None).await?,
    };

    let published_at = match existing {
        Some(page) => {
            if status == "published" && page.published_at.is_none() {
                Some(dates::now())
            } else {
                page.published_at
            }
        }
        None => (status == "published").then(dates::now),
    };

    Ok(PostInput {
        title: form.str("title"),
        slug,
        reading_time: existing.map(|p| p.reading_time).unwrap_or(1),
        html,
        plaintext: Some(plaintext),
        markdown: if format == "markdown" { content } else { None },
        custom_excerpt: form.opt("custom_excerpt"),
        feature_image: form.opt("feature_image"),
        feature_image_alt: form.opt("feature_image_alt"),
        feature_image_caption: form.opt("feature_image_caption"),
        kind: "page".into(),
        status,
        published_at,
        meta_title: form.opt("meta_title"),
        meta_description: form.opt("meta_description"),
        og_image: existing.and_then(|p| p.og_image.clone()),
        twitter_image: existing.and_then(|p| p.twitter_image.clone()),
        gallery_id: existing.and_then(|p| p.gallery_id),
        ..Default::default()
    })
}

/// `POST /dashboard/pages`.
pub async fn store(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let v = validate(&form);
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    let input = build_input(&ctx, &form, None).await?;
    Post::create(ctx.db(), &input).await?;
    ctx.redirect_with_success("/dashboard/pages", "Page created.")
        .await
}

/// `PUT /dashboard/pages/{id}`.
pub async fn update(ctx: Ctx, Path(id): Path<i64>, form: FormData) -> Result<Response, AppError> {
    let db = ctx.db();
    let page = Post::find(db, id).await?.ok_or(AppError::NotFound)?;
    let v = validate(&form);
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    let input = build_input(&ctx, &form, Some(&page)).await?;
    Post::update(db, page.id, &input).await?;
    ctx.redirect_with_success("/dashboard/pages", "Page updated.")
        .await
}

/// `DELETE /dashboard/pages/{id}`.
pub async fn destroy(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let db = ctx.db();
    let page = Post::find(db, id).await?.ok_or(AppError::NotFound)?;
    Post::delete(db, page.id).await?;
    ctx.redirect_with_success("/dashboard/pages", "Page deleted.")
        .await
}
