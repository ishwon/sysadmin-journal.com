use axum::http::StatusCode;
use axum::response::{Html, IntoResponse, Response};

#[derive(Debug)]
pub enum AppError {
    NotFound,
    Forbidden,
    Db(sqlx::Error),
    Other(anyhow::Error),
}

impl From<sqlx::Error> for AppError {
    fn from(e: sqlx::Error) -> Self {
        match e {
            sqlx::Error::RowNotFound => AppError::NotFound,
            other => AppError::Db(other),
        }
    }
}

impl From<anyhow::Error> for AppError {
    fn from(e: anyhow::Error) -> Self {
        AppError::Other(e)
    }
}

impl From<tower_sessions::session::Error> for AppError {
    fn from(e: tower_sessions::session::Error) -> Self {
        AppError::Other(anyhow::anyhow!("session error: {e}"))
    }
}

impl From<std::io::Error> for AppError {
    fn from(e: std::io::Error) -> Self {
        AppError::Other(e.into())
    }
}

impl IntoResponse for AppError {
    fn into_response(self) -> Response {
        match self {
            AppError::NotFound => error_page(StatusCode::NOT_FOUND, "Not Found"),
            AppError::Forbidden => error_page(StatusCode::FORBIDDEN, "Forbidden"),
            AppError::Db(e) => {
                tracing::error!(error = %e, "database error");
                error_page(StatusCode::INTERNAL_SERVER_ERROR, "Server Error")
            }
            AppError::Other(e) => {
                tracing::error!(error = %e, "request failed");
                error_page(StatusCode::INTERNAL_SERVER_ERROR, "Server Error")
            }
        }
    }
}

pub fn error_page(status: StatusCode, title: &str) -> Response {
    let body = format!(
        "<!DOCTYPE html>\n<html lang=\"en\"><head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"><title>{code} {title}</title>\
         <style>body{{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'JetBrains Mono',ui-monospace,monospace;color:#6b7596;background:#fff}}\
         .code{{font-size:1rem;letter-spacing:.06em;padding-right:1rem;border-right:1px solid #e6e8f0;margin-right:1rem}}h1{{font:400 1rem inherit;margin:0}}</style></head>\
         <body><span class=\"code\">{code}</span><h1>{title}</h1></body></html>",
        code = status.as_u16(),
        title = title
    );
    (status, Html(body)).into_response()
}
