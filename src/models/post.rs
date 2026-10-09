use std::collections::HashMap;

use chrono::NaiveDateTime;
use serde::{Deserialize, Serialize};
use sqlx::sqlite::SqliteArguments;
use sqlx::{Arguments, FromRow};

use super::gallery::{Gallery, GalleryWithImages};
use super::tag::Tag;
use super::user::User;
use crate::db::Db;
use crate::support::dates::{now, to_db};
use crate::support::text;

#[derive(Clone, Debug, FromRow, Serialize, Deserialize)]
pub struct Post {
    pub id: i64,
    pub title: String,
    pub slug: String,
    pub html: Option<String>,
    pub plaintext: Option<String>,
    pub markdown: Option<String>,
    pub custom_excerpt: Option<String>,
    pub feature_image: Option<String>,
    pub feature_image_alt: Option<String>,
    pub feature_image_caption: Option<String>,
    #[sqlx(rename = "type")]
    #[serde(rename = "type")]
    pub kind: String,
    pub status: String,
    pub reading_time: i64,
    #[serde(with = "super::db_datetime")]
    pub published_at: Option<NaiveDateTime>,
    pub meta_title: Option<String>,
    pub meta_description: Option<String>,
    pub og_image: Option<String>,
    pub og_title: Option<String>,
    pub og_description: Option<String>,
    pub twitter_image: Option<String>,
    pub twitter_title: Option<String>,
    pub twitter_description: Option<String>,
    pub canonical_url: Option<String>,
    pub ghost_id: Option<String>,
    #[serde(with = "super::db_datetime")]
    pub created_at: Option<NaiveDateTime>,
    #[serde(with = "super::db_datetime")]
    pub updated_at: Option<NaiveDateTime>,
    pub gallery_id: Option<i64>,
}

/// A post with its relations and the computed attributes the views use.
#[derive(Clone, Debug, Serialize)]
pub struct PostView {
    #[serde(flatten)]
    pub post: Post,
    pub tags: Vec<Tag>,
    pub authors: Vec<User>,
    pub gallery: Option<GalleryWithImages>,
    pub excerpt: String,
    pub plain_excerpt: String,
    pub primary_tag: Option<Tag>,
    pub primary_author: Option<User>,
    pub exists: bool,
}

impl std::ops::Deref for PostView {
    type Target = Post;

    fn deref(&self) -> &Post {
        &self.post
    }
}

#[derive(Clone, Debug, Default)]
pub struct PostInput {
    pub title: String,
    pub slug: String,
    pub html: Option<String>,
    pub plaintext: Option<String>,
    pub markdown: Option<String>,
    pub custom_excerpt: Option<String>,
    pub feature_image: Option<String>,
    pub feature_image_alt: Option<String>,
    pub feature_image_caption: Option<String>,
    pub kind: String,
    pub status: String,
    pub reading_time: i64,
    pub published_at: Option<NaiveDateTime>,
    pub meta_title: Option<String>,
    pub meta_description: Option<String>,
    pub og_image: Option<String>,
    pub og_title: Option<String>,
    pub og_description: Option<String>,
    pub twitter_image: Option<String>,
    pub twitter_title: Option<String>,
    pub twitter_description: Option<String>,
    pub canonical_url: Option<String>,
    pub ghost_id: Option<String>,
    pub gallery_id: Option<i64>,
    /// Only the Ghost importer overrides timestamps.
    pub created_at: Option<NaiveDateTime>,
    pub updated_at: Option<NaiveDateTime>,
}

impl Post {
    /// `getExcerptAttribute()`: the custom excerpt or the first 200 chars of plaintext.
    pub fn excerpt(&self) -> String {
        match self.custom_excerpt.as_deref() {
            Some(e) if !e.is_empty() => e.to_string(),
            _ => text::limit(self.plaintext.as_deref().unwrap_or(""), 200),
        }
    }

    /// Excerpt with HTML stripped — safe for meta tags, search JSON, and JSON-LD.
    pub fn plain_excerpt(&self) -> String {
        text::strip_tags(&self.excerpt()).trim().to_string()
    }

    pub fn is_post(&self) -> bool {
        self.kind == "post"
    }

    /// Build the view model, loading tags, authors and the attached gallery.
    pub async fn into_view(self, db: &Db) -> sqlx::Result<PostView> {
        let mut views = Self::load_relations(db, vec![self]).await?;
        let mut view = views.remove(0);
        if let Some(gallery_id) = view.post.gallery_id
            && let Some(gallery) = Gallery::find(db, gallery_id).await?
        {
            view.gallery = Some(gallery.with_images(db).await?);
        }
        Ok(view)
    }

    /// Eager-load tags and authors for a batch of posts (`with(['tags', 'authors'])`).
    pub async fn load_relations(db: &Db, posts: Vec<Post>) -> sqlx::Result<Vec<PostView>> {
        if posts.is_empty() {
            return Ok(Vec::new());
        }
        let ids: Vec<i64> = posts.iter().map(|p| p.id).collect();
        let marks = super::placeholders(ids.len());

        #[derive(FromRow)]
        struct TagRow {
            post_id: i64,
            #[sqlx(flatten)]
            tag: Tag,
        }
        #[derive(FromRow)]
        struct AuthorRow {
            post_id: i64,
            #[sqlx(flatten)]
            user: User,
        }

        let tag_sql = format!(
            "SELECT post_tag.post_id, tags.* FROM tags INNER JOIN post_tag ON post_tag.tag_id = tags.id
             WHERE post_tag.post_id IN ({marks}) ORDER BY post_tag.sort_order ASC, post_tag.id ASC"
        );
        let author_sql = format!(
            "SELECT post_user.post_id, users.* FROM users INNER JOIN post_user ON post_user.user_id = users.id
             WHERE post_user.post_id IN ({marks}) ORDER BY post_user.sort_order ASC, post_user.id ASC"
        );
        let mut tag_query = sqlx::query_as::<_, TagRow>(&tag_sql);
        let mut author_query = sqlx::query_as::<_, AuthorRow>(&author_sql);
        for id in &ids {
            tag_query = tag_query.bind(*id);
            author_query = author_query.bind(*id);
        }

        let mut tags: HashMap<i64, Vec<Tag>> = HashMap::new();
        for row in tag_query.fetch_all(db).await? {
            tags.entry(row.post_id).or_default().push(row.tag);
        }
        let mut authors: HashMap<i64, Vec<User>> = HashMap::new();
        for row in author_query.fetch_all(db).await? {
            authors.entry(row.post_id).or_default().push(row.user);
        }

        Ok(posts
            .into_iter()
            .map(|post| {
                let tags = tags.remove(&post.id).unwrap_or_default();
                let authors = authors.remove(&post.id).unwrap_or_default();
                PostView {
                    excerpt: post.excerpt(),
                    plain_excerpt: post.plain_excerpt(),
                    primary_tag: tags.first().cloned(),
                    primary_author: authors.first().cloned(),
                    tags,
                    authors,
                    gallery: None,
                    exists: true,
                    post,
                }
            })
            .collect())
    }

    pub async fn find(db: &Db, id: i64) -> sqlx::Result<Option<Post>> {
        sqlx::query_as("SELECT * FROM posts WHERE id = ?")
            .bind(id)
            .fetch_optional(db)
            .await
    }

    pub async fn find_by_slug(db: &Db, slug: &str) -> sqlx::Result<Option<Post>> {
        sqlx::query_as("SELECT * FROM posts WHERE slug = ?")
            .bind(slug)
            .fetch_optional(db)
            .await
    }

    pub async fn slug_taken(db: &Db, slug: &str, exclude_id: Option<i64>) -> sqlx::Result<bool> {
        let n: i64 = sqlx::query_scalar("SELECT COUNT(*) FROM posts WHERE slug = ? AND id != ?")
            .bind(slug)
            .bind(exclude_id.unwrap_or(0))
            .fetch_one(db)
            .await?;
        Ok(n > 0)
    }

    pub async fn tag_ids(db: &Db, post_id: i64) -> sqlx::Result<Vec<i64>> {
        sqlx::query_scalar(
            "SELECT tag_id FROM post_tag WHERE post_id = ? ORDER BY sort_order ASC, id ASC",
        )
        .bind(post_id)
        .fetch_all(db)
        .await
    }

    pub async fn create(db: &Db, input: &PostInput) -> sqlx::Result<i64> {
        let ts = now();
        let id = sqlx::query(
            "INSERT INTO posts (title, slug, html, plaintext, markdown, custom_excerpt, feature_image, feature_image_alt, feature_image_caption,
             type, status, reading_time, published_at, meta_title, meta_description, og_image, og_title, og_description, twitter_image,
             twitter_title, twitter_description, canonical_url, ghost_id, gallery_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        )
        .bind(&input.title)
        .bind(&input.slug)
        .bind(&input.html)
        .bind(&input.plaintext)
        .bind(&input.markdown)
        .bind(&input.custom_excerpt)
        .bind(&input.feature_image)
        .bind(&input.feature_image_alt)
        .bind(&input.feature_image_caption)
        .bind(&input.kind)
        .bind(&input.status)
        .bind(input.reading_time)
        .bind(input.published_at.map(to_db))
        .bind(&input.meta_title)
        .bind(&input.meta_description)
        .bind(&input.og_image)
        .bind(&input.og_title)
        .bind(&input.og_description)
        .bind(&input.twitter_image)
        .bind(&input.twitter_title)
        .bind(&input.twitter_description)
        .bind(&input.canonical_url)
        .bind(&input.ghost_id)
        .bind(input.gallery_id)
        .bind(to_db(input.created_at.unwrap_or(ts)))
        .bind(to_db(input.updated_at.unwrap_or(ts)))
        .execute(db)
        .await?
        .last_insert_rowid();
        Ok(id)
    }

    pub async fn update(db: &Db, id: i64, input: &PostInput) -> sqlx::Result<()> {
        sqlx::query(
            "UPDATE posts SET title = ?, slug = ?, html = ?, plaintext = ?, markdown = ?, custom_excerpt = ?, feature_image = ?,
             feature_image_alt = ?, feature_image_caption = ?, status = ?, reading_time = ?, published_at = ?, meta_title = ?,
             meta_description = ?, og_image = ?, twitter_image = ?, gallery_id = ?, updated_at = ? WHERE id = ?",
        )
        .bind(&input.title)
        .bind(&input.slug)
        .bind(&input.html)
        .bind(&input.plaintext)
        .bind(&input.markdown)
        .bind(&input.custom_excerpt)
        .bind(&input.feature_image)
        .bind(&input.feature_image_alt)
        .bind(&input.feature_image_caption)
        .bind(&input.status)
        .bind(input.reading_time)
        .bind(input.published_at.map(to_db))
        .bind(&input.meta_title)
        .bind(&input.meta_description)
        .bind(&input.og_image)
        .bind(&input.twitter_image)
        .bind(input.gallery_id)
        .bind(to_db(now()))
        .bind(id)
        .execute(db)
        .await?;
        Ok(())
    }

    /// Overwrite just the rendered body (used by `posts-wrap-figures`).
    pub async fn update_html(db: &Db, id: i64, html: &str, plaintext: &str) -> sqlx::Result<()> {
        sqlx::query("UPDATE posts SET html = ?, plaintext = ?, updated_at = ? WHERE id = ?")
            .bind(html)
            .bind(plaintext)
            .bind(to_db(now()))
            .bind(id)
            .execute(db)
            .await?;
        Ok(())
    }

    pub async fn delete(db: &Db, id: i64) -> sqlx::Result<()> {
        let mut tx = db.begin().await?;
        sqlx::query("DELETE FROM post_tag WHERE post_id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        sqlx::query("DELETE FROM post_user WHERE post_id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        sqlx::query("DELETE FROM posts WHERE id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        tx.commit().await
    }

    /// `$post->tags()->sync($ids)`.
    pub async fn sync_tags(db: &Db, post_id: i64, tag_ids: &[i64]) -> sqlx::Result<()> {
        let mut tx = db.begin().await?;
        sqlx::query("DELETE FROM post_tag WHERE post_id = ?")
            .bind(post_id)
            .execute(&mut *tx)
            .await?;
        for (index, tag_id) in tag_ids.iter().enumerate() {
            sqlx::query(
                "INSERT OR IGNORE INTO post_tag (post_id, tag_id, sort_order) VALUES (?, ?, ?)",
            )
            .bind(post_id)
            .bind(tag_id)
            .bind(index as i64)
            .execute(&mut *tx)
            .await?;
        }
        tx.commit().await
    }

    pub async fn attach_tag(
        db: &Db,
        post_id: i64,
        tag_id: i64,
        sort_order: i64,
    ) -> sqlx::Result<()> {
        sqlx::query(
            "INSERT OR IGNORE INTO post_tag (post_id, tag_id, sort_order) VALUES (?, ?, ?)",
        )
        .bind(post_id)
        .bind(tag_id)
        .bind(sort_order)
        .execute(db)
        .await?;
        Ok(())
    }

    pub async fn attach_author(
        db: &Db,
        post_id: i64,
        user_id: i64,
        sort_order: i64,
    ) -> sqlx::Result<()> {
        sqlx::query(
            "INSERT OR IGNORE INTO post_user (post_id, user_id, sort_order) VALUES (?, ?, ?)",
        )
        .bind(post_id)
        .bind(user_id)
        .bind(sort_order)
        .execute(db)
        .await?;
        Ok(())
    }
}

#[derive(Clone, Debug)]
enum Bind {
    Str(String),
    Int(i64),
}

#[derive(Clone, Copy, Debug)]
pub enum Order {
    PublishedDesc,
    PublishedAsc,
    UpdatedDesc,
}

/// A small query builder mirroring the Eloquent scopes used by the controllers.
#[derive(Clone, Debug)]
pub struct PostQuery {
    wheres: Vec<String>,
    binds: Vec<Bind>,
    order: Order,
}

impl Default for PostQuery {
    fn default() -> Self {
        Self::new()
    }
}

impl PostQuery {
    pub fn new() -> Self {
        Self {
            wheres: Vec::new(),
            binds: Vec::new(),
            order: Order::PublishedDesc,
        }
    }

    /// `scopePosts()`.
    pub fn posts(mut self) -> Self {
        self.wheres.push("posts.type = 'post'".into());
        self
    }

    /// `scopePages()`.
    pub fn pages(mut self) -> Self {
        self.wheres.push("posts.type = 'page'".into());
        self
    }

    /// `scopePublished()`.
    pub fn published(mut self) -> Self {
        self.wheres.push("posts.status = 'published' AND posts.published_at IS NOT NULL AND posts.published_at <= ?".into());
        self.binds.push(Bind::Str(to_db(now())));
        self
    }

    pub fn status(mut self, status: &str) -> Self {
        self.wheres.push("posts.status = ?".into());
        self.binds.push(Bind::Str(status.to_string()));
        self
    }

    pub fn tag(mut self, tag_id: i64) -> Self {
        self.wheres.push("EXISTS (SELECT 1 FROM post_tag WHERE post_tag.post_id = posts.id AND post_tag.tag_id = ?)".into());
        self.binds.push(Bind::Int(tag_id));
        self
    }

    pub fn author(mut self, user_id: i64) -> Self {
        self.wheres.push("EXISTS (SELECT 1 FROM post_user WHERE post_user.post_id = posts.id AND post_user.user_id = ?)".into());
        self.binds.push(Bind::Int(user_id));
        self
    }

    pub fn except(mut self, id: i64) -> Self {
        self.wheres.push("posts.id != ?".into());
        self.binds.push(Bind::Int(id));
        self
    }

    pub fn search(mut self, term: &str) -> Self {
        self.wheres
            .push("(posts.title LIKE ? OR posts.plaintext LIKE ?)".into());
        let like = format!("%{term}%");
        self.binds.push(Bind::Str(like.clone()));
        self.binds.push(Bind::Str(like));
        self
    }

    pub fn published_before(mut self, dt: NaiveDateTime) -> Self {
        self.wheres.push("posts.published_at < ?".into());
        self.binds.push(Bind::Str(to_db(dt)));
        self
    }

    pub fn published_after(mut self, dt: NaiveDateTime) -> Self {
        self.wheres.push("posts.published_at > ?".into());
        self.binds.push(Bind::Str(to_db(dt)));
        self
    }

    pub fn order(mut self, order: Order) -> Self {
        self.order = order;
        self
    }

    fn where_sql(&self) -> String {
        if self.wheres.is_empty() {
            String::new()
        } else {
            format!(" WHERE {}", self.wheres.join(" AND "))
        }
    }

    fn order_sql(&self) -> &'static str {
        match self.order {
            Order::PublishedDesc => " ORDER BY posts.published_at DESC, posts.id DESC",
            Order::PublishedAsc => " ORDER BY posts.published_at ASC, posts.id ASC",
            Order::UpdatedDesc => " ORDER BY posts.updated_at DESC, posts.id DESC",
        }
    }

    fn arguments(&self) -> SqliteArguments<'static> {
        let mut args = SqliteArguments::default();
        for bind in &self.binds {
            match bind {
                Bind::Str(s) => args.add(s.clone()).expect("bind"),
                Bind::Int(i) => args.add(*i).expect("bind"),
            }
        }
        args
    }

    pub async fn count(&self, db: &Db) -> sqlx::Result<i64> {
        let sql = format!("SELECT COUNT(*) FROM posts{}", self.where_sql());
        sqlx::query_scalar_with(&sql, self.arguments())
            .fetch_one(db)
            .await
    }

    pub async fn all(&self, db: &Db) -> sqlx::Result<Vec<Post>> {
        let sql = format!(
            "SELECT * FROM posts{}{}",
            self.where_sql(),
            self.order_sql()
        );
        sqlx::query_as_with(&sql, self.arguments())
            .fetch_all(db)
            .await
    }

    pub async fn limit(&self, db: &Db, limit: i64) -> sqlx::Result<Vec<Post>> {
        let sql = format!(
            "SELECT * FROM posts{}{} LIMIT {limit}",
            self.where_sql(),
            self.order_sql()
        );
        sqlx::query_as_with(&sql, self.arguments())
            .fetch_all(db)
            .await
    }

    pub async fn first(&self, db: &Db) -> sqlx::Result<Option<Post>> {
        let sql = format!(
            "SELECT * FROM posts{}{} LIMIT 1",
            self.where_sql(),
            self.order_sql()
        );
        sqlx::query_as_with(&sql, self.arguments())
            .fetch_optional(db)
            .await
    }

    /// One page of results and the total count (`paginate()`).
    pub async fn paginate(
        &self,
        db: &Db,
        per_page: i64,
        page: i64,
    ) -> sqlx::Result<(Vec<Post>, i64)> {
        let total = self.count(db).await?;
        let sql = format!(
            "SELECT * FROM posts{}{} LIMIT {per_page} OFFSET {}",
            self.where_sql(),
            self.order_sql(),
            (page - 1) * per_page
        );
        let rows = sqlx::query_as_with(&sql, self.arguments())
            .fetch_all(db)
            .await?;
        Ok((rows, total))
    }
}
