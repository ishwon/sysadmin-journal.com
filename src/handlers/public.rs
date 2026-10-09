use axum::extract::Path;
use axum::response::{IntoResponse, Response};
use tower::ServiceExt;
use tower_http::services::ServeFile;

use crate::http::{AppError, Ctx};
use crate::models::gallery::Gallery;
use crate::models::post::{Order, Post, PostQuery};
use crate::models::tag::Tag;
use crate::models::user::User;
use crate::support::pagination::Page;
use crate::views::layouts::BrandLayout;
use crate::views::pages::{
    AuthorShow, BrandSystem, GalleriesIndex, GalleryShow, PageShow, PostShow, PostsIndex, TagShow,
};
use crate::views::{Seo, render};

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

    let page = ctx.page_number();
    let (posts, total) = query.paginate(db, PER_PAGE, page).await?;
    let posts = Post::load_relations(db, posts).await?;
    let posts = Page::new(posts, total, PER_PAGE, page, &ctx.path, &[]);

    let featured = match featured {
        Some(f) if posts.on_first_page => Some(f.into_view(db).await?),
        _ => None,
    };

    let base = ctx.base();
    let seo = Seo::new(&base, "SysAdmin Journal", "Thoughts, ideas and stories");
    let content = render(PostsIndex { featured, posts })?;
    ctx.page(&base, &seo, None, content)
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

    let base = ctx.base();
    let seo_title = view
        .meta_title
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| format!("{} - SysAdmin Journal", view.title));
    let seo_description = view
        .meta_description
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| view.plain_excerpt.clone());
    let image = view
        .og_image
        .clone()
        .filter(|s| !s.is_empty())
        .or_else(|| view.feature_image.clone().filter(|s| !s.is_empty()));
    let twitter_image = view
        .twitter_image
        .clone()
        .filter(|s| !s.is_empty())
        .or_else(|| image.clone());
    let canonical = view
        .canonical_url
        .clone()
        .filter(|s| !s.is_empty())
        .unwrap_or_else(|| base.url(&format!("/{}", view.slug)));
    let seo = Seo::new(&base, seo_title, seo_description).for_post(
        &base,
        &view,
        image,
        twitter_image,
        canonical,
    );

    if !view.is_post() {
        let content = render(PageShow { post: view })?;
        return ctx.page(&base, &seo, None, content);
    }

    let (previous, next) = match view.published_at {
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
    };

    let content = render(PostShow {
        post: view,
        previous,
        next,
    })?;
    ctx.page(&base, &seo, None, content)
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

    let page = ctx.page_number();
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

    let base = ctx.base();
    let seo = Seo::new(
        &base,
        format!(
            "{} - SysAdmin Journal",
            tag.meta_title
                .clone()
                .filter(|s| !s.is_empty())
                .unwrap_or_else(|| tag.name.clone())
        ),
        tag.meta_description
            .clone()
            .filter(|s| !s.is_empty())
            .unwrap_or_else(|| format!("Posts tagged with {}", tag.name)),
    );
    let feed_url = base.url(&format!("/tag/{}/rss", tag.slug));
    let content = render(TagShow { tag, posts })?;
    ctx.page(&base, &seo, Some(feed_url), content)
}

/// `GET /author/{slug}`.
pub async fn author(ctx: Ctx, Path(slug): Path<String>) -> Result<Response, AppError> {
    let db = ctx.db();
    let author = User::find_by_slug(db, &slug)
        .await?
        .ok_or(AppError::NotFound)?;

    let page = ctx.page_number();
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

    let base = ctx.base();
    let seo = Seo::new(
        &base,
        format!("{} - SysAdmin Journal", author.name),
        author
            .bio
            .clone()
            .filter(|s| !s.is_empty())
            .unwrap_or_else(|| format!("Posts by {}", author.name)),
    );
    let content = render(AuthorShow { author, posts })?;
    ctx.page(&base, &seo, None, content)
}

/// `GET /gallery`.
pub async fn galleries(ctx: Ctx) -> Result<Response, AppError> {
    let galleries = Gallery::all_with_counts(ctx.db()).await?;
    let base = ctx.base();
    let seo = Seo::new(&base, "Gallery - SysAdmin Journal", "Photo collections");
    let content = render(GalleriesIndex { galleries })?;
    ctx.page(&base, &seo, None, content)
}

/// `GET /gallery/{slug}`.
pub async fn gallery(ctx: Ctx, Path(slug): Path<String>) -> Result<Response, AppError> {
    let db = ctx.db();
    let gallery = Gallery::find_by_slug(db, &slug)
        .await?
        .ok_or(AppError::NotFound)?
        .with_images(db)
        .await?;
    let base = ctx.base();
    let seo = Seo::new(
        &base,
        format!("{} - SysAdmin Journal", gallery.title),
        gallery
            .description
            .clone()
            .filter(|s| !s.is_empty())
            .unwrap_or_else(|| gallery.title.clone()),
    );
    let content = render(GalleryShow { gallery })?;
    ctx.page(&base, &seo, None, content)
}

/// `GET /brand-system` — the static design reference page.
pub async fn brand_system(ctx: Ctx) -> Result<Response, AppError> {
    let base = ctx.base();
    let content = render(BrandSystem)?;
    let html = render(BrandLayout {
        base: &base,
        content,
    })?;
    Ok(axum::response::Html(html).into_response())
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
