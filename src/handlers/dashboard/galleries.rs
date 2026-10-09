use axum::extract::Path;
use axum::response::Response;

use super::PER_PAGE;
use crate::http::form::FormData;
use crate::http::validation::Validator;
use crate::http::{AppError, Ctx};
use crate::models::gallery::{Gallery, GalleryImageInput, GalleryInput, GalleryWithImages};
use crate::support::pagination::Page;
use crate::support::text;
use crate::views::layouts::DashboardPage;
use crate::views::pages::{GalleriesAdmin, GalleryForm};
use crate::views::{render, ui};

/// `GET /dashboard/galleries`.
pub async fn index(ctx: Ctx) -> Result<Response, AppError> {
    let page = ctx.page_number();
    let (galleries, total) = Gallery::paginate_with_counts(ctx.db(), PER_PAGE, page).await?;
    let galleries = Page::new(galleries, total, PER_PAGE, page, &ctx.path, &ctx.query);
    let base = ctx.base();
    let content = render(GalleriesAdmin {
        base: &base,
        galleries,
    })?;
    let page = DashboardPage::new("Galleries", content)
        .heading("Galleries")
        .actions(
            ui::button("primary")
                .size("sm")
                .href("/dashboard/galleries/create")
                .html("New gallery"),
        );
    ctx.dashboard(&base, page).await
}

async fn form_page(
    ctx: &Ctx,
    title: &str,
    gallery: Option<GalleryWithImages>,
) -> Result<Response, AppError> {
    let images_initial: Vec<GalleryImageInput> = gallery
        .as_ref()
        .map(|g| {
            g.images
                .iter()
                .map(|i| GalleryImageInput {
                    path: i.image_path.clone(),
                    caption: i.caption.clone(),
                    alt_text: i.alt_text.clone(),
                })
                .collect()
        })
        .unwrap_or_default();
    let (action, editing) = match &gallery {
        Some(g) => (format!("/dashboard/galleries/{}", g.id), true),
        None => ("/dashboard/galleries".to_string(), false),
    };
    let base = ctx.base();
    let content = render(GalleryForm {
        base: &base,
        gallery,
        images_initial,
        action,
        editing,
    })?;
    ctx.dashboard(&base, DashboardPage::new(title, content))
        .await
}

/// `GET /dashboard/galleries/create`.
pub async fn create(ctx: Ctx) -> Result<Response, AppError> {
    form_page(&ctx, "New Gallery", None).await
}

/// `GET /dashboard/galleries/{id}/edit`.
pub async fn edit(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let db = ctx.db();
    let gallery = Gallery::find(db, id)
        .await?
        .ok_or(AppError::NotFound)?
        .with_images(db)
        .await?;
    form_page(&ctx, "Edit Gallery", Some(gallery)).await
}

fn validate(form: &FormData) -> (Validator, Vec<GalleryImageInput>) {
    let mut v = Validator::new();
    v.required(form, "title").max(form, "title", 255);

    let mut images = Vec::new();
    for (index, row) in form.rows("images").into_iter().enumerate() {
        let path = row.get("path").cloned().unwrap_or_default();
        if path.trim().is_empty() {
            v.fail(format!("The images.{index}.path field is required."));
            continue;
        }
        images.push(GalleryImageInput {
            path,
            caption: row.get("caption").cloned().filter(|s| !s.is_empty()),
            alt_text: row.get("alt_text").cloned().filter(|s| !s.is_empty()),
        });
    }
    (v, images)
}

/// `POST /dashboard/galleries`.
pub async fn store(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let (v, images) = validate(&form);
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    let title = form.str("title");
    let input = GalleryInput {
        slug: text::slug(&title),
        title,
        description: form.opt("description"),
        cover_image: form.opt("cover_image"),
        images,
    };
    Gallery::create(ctx.db(), &input).await?;
    ctx.redirect_with_success("/dashboard/galleries", "Gallery created.")
        .await
}

/// `PUT /dashboard/galleries/{id}`.
pub async fn update(ctx: Ctx, Path(id): Path<i64>, form: FormData) -> Result<Response, AppError> {
    let db = ctx.db();
    let gallery = Gallery::find(db, id).await?.ok_or(AppError::NotFound)?;
    let (v, images) = validate(&form);
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    let input = GalleryInput {
        title: form.str("title"),
        slug: gallery.slug.clone(),
        description: form.opt("description"),
        cover_image: form.opt("cover_image"),
        images,
    };
    Gallery::update(db, gallery.id, &input).await?;
    ctx.redirect_with_success("/dashboard/galleries", "Gallery updated.")
        .await
}

/// `DELETE /dashboard/galleries/{id}`.
pub async fn destroy(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let db = ctx.db();
    let gallery = Gallery::find(db, id).await?.ok_or(AppError::NotFound)?;
    Gallery::delete(db, gallery.id).await?;
    ctx.redirect_with_success("/dashboard/galleries", "Gallery deleted.")
        .await
}
