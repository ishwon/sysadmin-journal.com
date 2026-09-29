use axum::extract::Path;
use axum::response::Response;
use minijinja::context;

use super::PER_PAGE;
use crate::http::form::FormData;
use crate::http::validation::Validator;
use crate::http::{AppError, Ctx};
use crate::models::user::{User, UserInput};
use crate::support::pagination::Page;
use crate::support::text;

/// `GET /dashboard/users`.
pub async fn index(ctx: Ctx) -> Result<Response, AppError> {
    let page = ctx.page();
    let (users, total) = User::paginate_with_counts(ctx.db(), PER_PAGE, page).await?;
    let users = Page::new(users, total, PER_PAGE, page, &ctx.path, &ctx.query);
    ctx.render_dashboard(
        "dashboard/users/index.html",
        context! { title => "Users", page_title => "Users", users },
    )
    .await
}

/// `GET /dashboard/users/create`.
pub async fn create(ctx: Ctx) -> Result<Response, AppError> {
    ctx.render_dashboard(
        "dashboard/users/create.html",
        context! { title => "New User", user => minijinja::Value::UNDEFINED },
    )
    .await
}

/// `GET /dashboard/users/{id}/edit`.
pub async fn edit(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let user = User::find(ctx.db(), id).await?.ok_or(AppError::NotFound)?;
    ctx.render_dashboard(
        "dashboard/users/edit.html",
        context! { title => "Edit User", user },
    )
    .await
}

async fn validate(
    ctx: &Ctx,
    form: &FormData,
    exclude_id: Option<i64>,
) -> Result<Validator, AppError> {
    let db = ctx.db();
    let mut v = Validator::new();
    v.required(form, "name")
        .max(form, "name", 255)
        .required(form, "slug")
        .max(form, "slug", 255)
        .required(form, "email")
        .email(form, "email")
        .max(form, "website", 255)
        .max(form, "location", 255)
        .max(form, "facebook", 255)
        .max(form, "twitter", 255);
    if exclude_id.is_none() {
        v.required(form, "password");
    }
    v.min(form, "password", 8);
    if let Some(slug) = form.opt("slug") {
        v.taken("slug", User::slug_taken(db, &slug, exclude_id).await?);
    }
    if let Some(email) = form.opt("email") {
        v.taken("email", User::email_taken(db, &email, exclude_id).await?);
    }
    Ok(v)
}

fn input(ctx: &Ctx, form: &FormData) -> Result<UserInput, AppError> {
    let password = match form.opt("password") {
        Some(p) => Some(
            bcrypt::hash(p, ctx.state.config.bcrypt_rounds)
                .map_err(|e| anyhow::anyhow!("bcrypt: {e}"))?,
        ),
        None => None,
    };
    Ok(UserInput {
        name: form.str("name"),
        slug: text::slug(&form.str("slug")),
        email: form.str("email"),
        password,
        bio: form.opt("bio"),
        profile_image: form.opt("profile_image"),
        website: form.opt("website"),
        location: form.opt("location"),
        facebook: form.opt("facebook"),
        twitter: form.opt("twitter"),
        ghost_id: None,
    })
}

/// `POST /dashboard/users`.
pub async fn store(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let v = validate(&ctx, &form, None).await?;
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    User::create(ctx.db(), &input(&ctx, &form)?).await?;
    ctx.redirect_with_success("/dashboard/users", "User created.")
        .await
}

/// `PUT /dashboard/users/{id}`.
pub async fn update(ctx: Ctx, Path(id): Path<i64>, form: FormData) -> Result<Response, AppError> {
    let db = ctx.db();
    let user = User::find(db, id).await?.ok_or(AppError::NotFound)?;
    let v = validate(&ctx, &form, Some(user.id)).await?;
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }
    User::update(db, user.id, &input(&ctx, &form)?).await?;
    ctx.redirect_with_success("/dashboard/users", "User updated.")
        .await
}

/// `DELETE /dashboard/users/{id}`.
pub async fn destroy(ctx: Ctx, Path(id): Path<i64>) -> Result<Response, AppError> {
    let db = ctx.db();
    let user = User::find(db, id).await?.ok_or(AppError::NotFound)?;
    if user.id == ctx.auth()?.id {
        return ctx
            .back_with_errors(vec!["Cannot delete your own account.".into()], None)
            .await;
    }
    User::delete(db, user.id).await?;
    ctx.redirect_with_success("/dashboard/users", "User deleted.")
        .await
}
