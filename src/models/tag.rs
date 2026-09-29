use chrono::NaiveDateTime;
use serde::{Deserialize, Serialize};
use sqlx::FromRow;

use crate::db::Db;
use crate::support::dates::{now, to_db};

#[derive(Clone, Debug, FromRow, Serialize, Deserialize)]
pub struct Tag {
    pub id: i64,
    pub name: String,
    pub slug: String,
    pub description: Option<String>,
    pub feature_image: Option<String>,
    pub meta_title: Option<String>,
    pub meta_description: Option<String>,
    pub ghost_id: Option<String>,
    #[serde(with = "super::db_datetime")]
    pub created_at: Option<NaiveDateTime>,
    #[serde(with = "super::db_datetime")]
    pub updated_at: Option<NaiveDateTime>,
}

#[derive(Clone, Debug, FromRow, Serialize)]
pub struct TagWithCount {
    #[sqlx(flatten)]
    #[serde(flatten)]
    pub tag: Tag,
    pub posts_count: i64,
}

#[derive(Clone, Debug, Default)]
pub struct TagInput {
    pub name: String,
    pub slug: String,
    pub description: Option<String>,
    pub feature_image: Option<String>,
    pub meta_title: Option<String>,
    pub meta_description: Option<String>,
    pub ghost_id: Option<String>,
}

impl Tag {
    pub async fn find(db: &Db, id: i64) -> sqlx::Result<Option<Tag>> {
        sqlx::query_as("SELECT * FROM tags WHERE id = ?")
            .bind(id)
            .fetch_optional(db)
            .await
    }

    pub async fn find_by_slug(db: &Db, slug: &str) -> sqlx::Result<Option<Tag>> {
        sqlx::query_as("SELECT * FROM tags WHERE slug = ?")
            .bind(slug)
            .fetch_optional(db)
            .await
    }

    pub async fn all_by_name(db: &Db) -> sqlx::Result<Vec<Tag>> {
        sqlx::query_as("SELECT * FROM tags ORDER BY name ASC")
            .fetch_all(db)
            .await
    }

    pub async fn count(db: &Db) -> sqlx::Result<i64> {
        sqlx::query_scalar("SELECT COUNT(*) FROM tags")
            .fetch_one(db)
            .await
    }

    pub async fn slug_taken(db: &Db, slug: &str, exclude_id: Option<i64>) -> sqlx::Result<bool> {
        let n: i64 = sqlx::query_scalar("SELECT COUNT(*) FROM tags WHERE slug = ? AND id != ?")
            .bind(slug)
            .bind(exclude_id.unwrap_or(0))
            .fetch_one(db)
            .await?;
        Ok(n > 0)
    }

    pub async fn paginate_with_counts(
        db: &Db,
        per_page: i64,
        page: i64,
    ) -> sqlx::Result<(Vec<TagWithCount>, i64)> {
        let total = Self::count(db).await?;
        let rows = sqlx::query_as(
            "SELECT tags.*, (SELECT COUNT(*) FROM post_tag WHERE post_tag.tag_id = tags.id) AS posts_count
             FROM tags ORDER BY name ASC LIMIT ? OFFSET ?",
        )
        .bind(per_page)
        .bind((page - 1) * per_page)
        .fetch_all(db)
        .await?;
        Ok((rows, total))
    }

    /// `Tag::has('posts')->get()`.
    pub async fn with_posts(db: &Db) -> sqlx::Result<Vec<Tag>> {
        sqlx::query_as("SELECT * FROM tags WHERE EXISTS (SELECT 1 FROM post_tag WHERE post_tag.tag_id = tags.id) ORDER BY id")
            .fetch_all(db)
            .await
    }

    pub async fn create(db: &Db, input: &TagInput) -> sqlx::Result<i64> {
        let ts = to_db(now());
        let id = sqlx::query(
            "INSERT INTO tags (name, slug, description, feature_image, meta_title, meta_description, ghost_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        )
        .bind(&input.name)
        .bind(&input.slug)
        .bind(&input.description)
        .bind(&input.feature_image)
        .bind(&input.meta_title)
        .bind(&input.meta_description)
        .bind(&input.ghost_id)
        .bind(&ts)
        .bind(&ts)
        .execute(db)
        .await?
        .last_insert_rowid();
        Ok(id)
    }

    pub async fn update(db: &Db, id: i64, input: &TagInput) -> sqlx::Result<()> {
        sqlx::query(
            "UPDATE tags SET name = ?, slug = ?, description = ?, feature_image = ?, meta_title = ?, meta_description = ?, updated_at = ? WHERE id = ?",
        )
        .bind(&input.name)
        .bind(&input.slug)
        .bind(&input.description)
        .bind(&input.feature_image)
        .bind(&input.meta_title)
        .bind(&input.meta_description)
        .bind(to_db(now()))
        .bind(id)
        .execute(db)
        .await?;
        Ok(())
    }

    pub async fn delete(db: &Db, id: i64) -> sqlx::Result<()> {
        let mut tx = db.begin().await?;
        sqlx::query("DELETE FROM post_tag WHERE tag_id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        sqlx::query("DELETE FROM tags WHERE id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        tx.commit().await
    }
}
