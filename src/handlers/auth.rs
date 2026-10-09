use axum::response::{Html, IntoResponse, Redirect, Response};

use crate::http::form::FormData;
use crate::http::middleware::{forget_cookie, remember_cookie};
use crate::http::validation::Validator;
use crate::http::{AppError, Ctx};
use crate::models::user::User;
use crate::support::text;
use crate::views::pages::Login;
use crate::views::render;

/// `GET /login`.
pub async fn show_login(ctx: Ctx) -> Result<Response, AppError> {
    if ctx.user.is_some() {
        return Ok(Redirect::to("/dashboard").into_response());
    }
    let base = ctx.base();
    Ok(Html(render(Login { base: &base })?).into_response())
}

/// `POST /login`.
pub async fn login(ctx: Ctx, form: FormData) -> Result<Response, AppError> {
    let mut v = Validator::new();
    v.required(&form, "email")
        .email(&form, "email")
        .required(&form, "password");
    if !v.is_ok() {
        return ctx.back_with_errors(v.errors, Some(&form)).await;
    }

    let email = form.str("email");
    let password = form.str("password");
    let user = User::find_by_email(ctx.db(), &email).await?;
    let valid = match &user {
        Some(user) => bcrypt::verify(&password, &user.password).unwrap_or(false),
        None => false,
    };

    let Some(user) = user.filter(|_| valid) else {
        // `onlyInput('email')`: never flash the password back.
        let only_email = FormData::from_pairs(vec![("email".into(), email)]);
        return ctx
            .back_with_errors(
                vec!["The provided credentials do not match our records.".into()],
                Some(&only_email),
            )
            .await;
    };

    ctx.session.cycle_id().await?;
    ctx.session.insert("user_id", user.id).await?;

    if form.boolean("remember") {
        let token = text::random(60);
        User::set_remember_token(ctx.db(), user.id, Some(&token)).await?;
        ctx.cookies
            .signed(&ctx.state.cookie_key)
            .add(remember_cookie(format!("{}|{token}", user.id)));
    }

    let intended = ctx
        .session
        .remove::<String>("intended")
        .await?
        .unwrap_or_else(|| "/dashboard".to_string());
    Ok(Redirect::to(&intended).into_response())
}

/// `POST /logout`.
pub async fn logout(ctx: Ctx) -> Result<Response, AppError> {
    if let Some(user) = &ctx.user
        && ctx
            .cookies
            .get(crate::http::middleware::REMEMBER_COOKIE)
            .is_some()
    {
        User::set_remember_token(ctx.db(), user.id, None).await?;
    }
    ctx.cookies.add(forget_cookie());
    ctx.session.flush().await?;
    Ok(Redirect::to("/").into_response())
}
