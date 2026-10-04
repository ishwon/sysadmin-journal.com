use axum::extract::Path;
use axum::response::Response;
use minijinja::context;

use super::PER_PAGE;
use crate::http::form::FormData;
use crate::http::validation::Validator;
use crate::http::{AppError, Ctx};
use crate::models::tag::{Tag, TagInput};
use crate::support::pagination::Page;
use crate::support::text;

/// `GET /dashboard/tags`.
pub async fn index(ctx: Ctx) -> Result<Response, AppError> {
    let page = ctx.page();
    let (tags, total) = Tag::paginate_with_counts(ctx.db(), PER_PAGE, page).await?;
    let tags = Page::new(tags, total, PER_PAGE, page, &ctx.path, &ctx.query);
    ctx.render_dashboard(
        "dashboard/tags/index.html",
        context! { title => "Tags", page_title => "Tags", tags },
    )
    .await
}

/// `GET /dashboard/tags/create`.
pub async fn create(ctx: Ctx) -> Result<Response, AppError> {
    ctx.render_dashboard(
        "dashboard/tags/create.html",
        context! { title => "New Tag", tag => minijinja::Value::UNDEFINED },
    )
    .await
}

/// `GET /dashboard/tags/{id}/edit`.
pub async fn edit(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let tag = Tag::find(ctx.db(), id).await?.ok_or(AppError::NotFound)?;
    ctx.render_dashboard(
        "dashboard/tags/edit.html",
        context! { title => "Edit Tag", tag },
    )
    .await
}

async fn validate(
    ctx: &Ctx,
    form: &FormData,
    exclude_id: Option<i64>,
) -> Result<Validator, AppError> {
    let mut v = Validator::new();
    v.required(form, "name")
        .max(form, "name", 255)
        .required(form, "slug")
        .max(form, "slug", 255)
        .max(form, "meta_title", 255);
    if let Some(slug) = form.opt("slug") {
        v.taken("slug", Tag::slug_taken(ctx.db(), &slug, exclude_id).await?);
    }
    Ok(v)
}

fn input(form: &FormData) -> TagInput {
    TagInput {
        name: form.str("name"),
        slug: text::slug(&form.str("slug")),
        description: form.opt("description"),
        feature_image: form.opt("feature_image"),
        meta_title: form.opt("meta_title"),
        meta_description: form.opt("meta_description"),
        ghost_id: None,
    }
}

/// `POST /dashboard/tags`.
pub async fn store(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let v = validate(&ctx, &form, None).await?;
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    Tag::create(ctx.db(), &input(&form)).await?;
    ctx.redirect_with_success("/dashboard/tags", "Tag created.")
        .await
}

/// `PUT /dashboard/tags/{id}`.
pub async fn update(ctx: Ctx, Path(id): Path<i64>, form: FormData) -> Result<Response, AppError> {
    let db = ctx.db();
    let tag = Tag::find(db, id).await?.ok_or(AppError::NotFound)?;
    let v = validate(&ctx, &form, Some(tag.id)).await?;
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    Tag::update(db, tag.id, &input(&form)).await?;
    ctx.redirect_with_success("/dashboard/tags", "Tag updated.")
        .await
}

/// `DELETE /dashboard/tags/{id}`.
pub async fn destroy(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let db = ctx.db();
    let tag = Tag::find(db, id).await?.ok_or(AppError::NotFound)?;
    Tag::delete(db, tag.id).await?;
    ctx.redirect_with_success("/dashboard/tags", "Tag deleted.")
        .await
}
