use axum::extract::Path;
use axum::response::{IntoResponse, Response};
use minijinja::context;
use tower::ServiceExt;
use tower_http::services::ServeFile;

use crate::http::{AppError, Ctx};
use crate::models::gallery::Gallery;
use crate::models::post::{Order, Post, PostQuery};
use crate::models::tag::Tag;
use crate::models::user::User;
use crate::support::pagination::Page;

const PER_PAGE: i64 = 9;

/// `GET /` — featured post (first page only) plus a paginated grid.
pub async fn home(ctx: Ctx) -> Result<Response, AppError> {
    let db = ctx.db();
    let published = PostQuery::new()
        .posts()
        .published()
        .order(Order::PublishedDesc);

    let featured = published.first(db).await?;
    let query = match &featured {
        Some(f) => published.clone().except(f.id),
        None => published.clone(),
    };

    let page = ctx.page();
    let (posts, total) = query.paginate(db, PER_PAGE, page).await?;
    let posts = Post::load_relations(db, posts).await?;
    let posts = Page::new(posts, total, PER_PAGE, page, &ctx.path, &[]);

    let featured = match featured {
        Some(f) if posts.on_first_page => Some(f.into_view(db).await?),
        _ => None,
    };

    ctx.render(
        "posts/index.html",
        context! {
            featured,
            posts,
            seo_title => "SysAdmin Journal",
            seo_description => "Thoughts, ideas and stories",
        },
    )
}

/// `GET /{slug}` — a published post or page. Falls back to files in `public/`
/// (favicon, robots.txt, generated sitemaps) so they keep their old URLs.
pub async fn show(
    ctx: Ctx,
    Path(slug): Path<String>,
    req: axum::extract::Request,
) -> Result<Response, AppError> {
    let db = ctx.db();
    let found = PostQuery::new()
        .published()
        .first_by_slug(db, &slug)
        .await?;

    let Some(post) = found else {
        return serve_public_file(&ctx, &slug, req).await;
    };

    let mut view = post.into_view(db).await?;
    view.post.html = super::render_gallery_shortcodes(db, view.post.html.take()).await?;

    let seo_title = view
        .post
        .meta_title
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| format!("{} - SysAdmin Journal", view.post.title));
    let seo_description = view
        .post
        .meta_description
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| view.plain_excerpt.clone());
    let seo_image = view
        .post
        .og_image
        .clone()
        .filter(|s| !s.is_empty())
        .or_else(|| view.post.feature_image.clone());
    let twitter_image = view
        .post
        .twitter_image
        .clone()
        .filter(|s| !s.is_empty())
        .or_else(|| view.post.og_image.clone().filter(|s| !s.is_empty()))
        .or_else(|| view.post.feature_image.clone());
    let canonical_url = view
        .post
        .canonical_url
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| ctx.state.config.url(&format!("/{}", view.post.slug)));
    let seo_type = if view.post.is_post() {
        "article"
    } else {
        "website"
    };

    let (previous, next) = if view.post.is_post() {
        match view.post.published_at {
            Some(at) => (
                PostQuery::new()
                    .posts()
                    .published()
                    .published_before(at)
                    .order(Order::PublishedDesc)
                    .first(db)
                    .await?,
                PostQuery::new()
                    .posts()
                    .published()
                    .published_after(at)
                    .order(Order::PublishedAsc)
                    .first(db)
                    .await?,
            ),
            None => (None, None),
        }
    } else {
        (None, None)
    };

    let template = if view.post.is_post() {
        "posts/show.html"
    } else {
        "pages/show.html"
    };

    ctx.render(
        template,
        context! {
            post => view,
            previous,
            next,
            seo_title,
            seo_description,
            seo_image,
            seo_type,
            twitter_image,
            canonical_url,
        },
    )
}

async fn serve_public_file(
    ctx: &Ctx,
    name: &str,
    req: axum::extract::Request,
) -> Result<Response, AppError> {
    // Only plain file names: no traversal, no hidden files.
    if name.is_empty() || name.contains(['/', '\\']) || name.starts_with('.') {
        return Err(AppError::NotFound);
    }
    let path = ctx.state.config.public_dir.join(name);
    if !path.is_file() {
        return Err(AppError::NotFound);
    }
    let response = ServeFile::new(path)
        .oneshot(req)
        .await
        .map_err(|e| AppError::Other(anyhow::anyhow!("static file: {e}")))?;
    Ok(response.into_response())
}

/// `GET /tag/{slug}`.
pub async fn tag(ctx: Ctx, Path(slug): Path<String>) -> Result<Response, AppError> {
    let db = ctx.db();
    let tag = Tag::find_by_slug(db, &slug)
        .await?
        .ok_or(AppError::NotFound)?;

    let page = ctx.page();
    let (posts, total) = PostQuery::new()
        .posts()
        .published()
        .tag(tag.id)
        .order(Order::PublishedDesc)
        .paginate(db, PER_PAGE, page)
        .await?;
    let posts = Page::new(
        Post::load_relations(db, posts).await?,
        total,
        PER_PAGE,
        page,
        &ctx.path,
        &[],
    );

    let seo_title = format!(
        "{} - SysAdmin Journal",
        tag.meta_title
            .clone()
            .filter(|s| !s.is_empty())
            .unwrap_or_else(|| tag.name.clone())
    );
    let seo_description = tag
        .meta_description
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| format!("Posts tagged with {}", tag.name));
    let feed_url = ctx.state.config.url(&format!("/tag/{}/rss", tag.slug));

    ctx.render(
        "tags/show.html",
        context! { tag, posts, seo_title, seo_description, feed_url },
    )
}

/// `GET /author/{slug}`.
pub async fn author(ctx: Ctx, Path(slug): Path<String>) -> Result<Response, AppError> {
    let db = ctx.db();
    let author = User::find_by_slug(db, &slug)
        .await?
        .ok_or(AppError::NotFound)?;

    let page = ctx.page();
    let (posts, total) = PostQuery::new()
        .posts()
        .published()
        .author(author.id)
        .order(Order::PublishedDesc)
        .paginate(db, PER_PAGE, page)
        .await?;
    let posts = Page::new(
        Post::load_relations(db, posts).await?,
        total,
        PER_PAGE,
        page,
        &ctx.path,
        &[],
    );

    let seo_title = format!("{} - SysAdmin Journal", author.name);
    let seo_description = author
        .bio
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| format!("Posts by {}", author.name));

    ctx.render(
        "authors/show.html",
        context! { author, posts, seo_title, seo_description },
    )
}

/// `GET /gallery`.
pub async fn galleries(ctx: Ctx) -> Result<Response, AppError> {
    let galleries = Gallery::all_with_counts(ctx.db()).await?;
    ctx.render(
        "galleries/index.html",
        context! { galleries, seo_title => "Gallery - SysAdmin Journal", seo_description => "Photo collections" },
    )
}

/// `GET /gallery/{slug}`.
pub async fn gallery(ctx: Ctx, Path(slug): Path<String>) -> Result<Response, AppError> {
    let db = ctx.db();
    let gallery = Gallery::find_by_slug(db, &slug)
        .await?
        .ok_or(AppError::NotFound)?
        .with_images(db)
        .await?;
    let seo_title = format!("{} - SysAdmin Journal", gallery.gallery.title);
    let seo_description = gallery
        .gallery
        .description
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| gallery.gallery.title.clone());
    ctx.render(
        "galleries/show.html",
        context! { gallery, seo_title, seo_description },
    )
}

/// `GET /brand-system` — the static design reference page.
pub async fn brand_system(ctx: Ctx) -> Result<Response, AppError> {
    ctx.render("brand-system.html", context! {})
}

impl PostQuery {
    /// `Post::published()->where('slug', $slug)->first()`.
    pub async fn first_by_slug(
        &self,
        db: &crate::db::Db,
        slug: &str,
    ) -> sqlx::Result<Option<Post>> {
        let Some(post) = Post::find_by_slug(db, slug).await? else {
            return Ok(None);
        };
        let published = post.status == "published"
            && post
                .published_at
                .map(|at| at <= crate::support::dates::now())
                .unwrap_or(false);
        Ok(published.then_some(post))
    }
}
