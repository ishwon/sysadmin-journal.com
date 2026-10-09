use chrono::NaiveDateTime;
use serde::{Deserialize, Serialize};
use sqlx::FromRow;

use crate::db::Db;
use crate::support::dates::{now, to_db};

#[derive(Clone, Debug, FromRow, Serialize, Deserialize)]
pub struct Gallery {
    pub id: i64,
    pub title: String,
    pub slug: String,
    pub description: Option<String>,
    pub cover_image: Option<String>,
    #[serde(with = "super::db_datetime")]
    pub created_at: Option<NaiveDateTime>,
    #[serde(with = "super::db_datetime")]
    pub updated_at: Option<NaiveDateTime>,
}

#[derive(Clone, Debug, FromRow, Serialize, Deserialize)]
pub struct GalleryImage {
    pub id: i64,
    pub gallery_id: i64,
    pub image_path: String,
    pub caption: Option<String>,
    pub alt_text: Option<String>,
    pub sort_order: i64,
}

#[derive(Clone, Debug, FromRow, Serialize)]
pub struct GalleryWithCount {
    #[sqlx(flatten)]
    #[serde(flatten)]
    pub gallery: Gallery,
    pub images_count: i64,
}

#[derive(Clone, Debug, Serialize)]
pub struct GalleryWithImages {
    #[serde(flatten)]
    pub gallery: Gallery,
    pub images: Vec<GalleryImage>,
}

impl std::ops::Deref for GalleryWithCount {
    type Target = Gallery;

    fn deref(&self) -> &Gallery {
        &self.gallery
    }
}

impl std::ops::Deref for GalleryWithImages {
    type Target = Gallery;

    fn deref(&self) -> &Gallery {
        &self.gallery
    }
}

#[derive(Clone, Debug, Default, Serialize, Deserialize)]
pub struct GalleryImageInput {
    pub path: String,
    pub caption: Option<String>,
    pub alt_text: Option<String>,
}

#[derive(Clone, Debug, Default)]
pub struct GalleryInput {
    pub title: String,
    pub slug: String,
    pub description: Option<String>,
    pub cover_image: Option<String>,
    pub images: Vec<GalleryImageInput>,
}

impl Gallery {
    pub async fn find(db: &Db, id: i64) -> sqlx::Result<Option<Gallery>> {
        sqlx::query_as("SELECT * FROM galleries WHERE id = ?")
            .bind(id)
            .fetch_optional(db)
            .await
    }

    pub async fn find_by_slug(db: &Db, slug: &str) -> sqlx::Result<Option<Gallery>> {
        sqlx::query_as("SELECT * FROM galleries WHERE slug = ?")
            .bind(slug)
            .fetch_optional(db)
            .await
    }

    pub async fn exists(db: &Db, id: i64) -> sqlx::Result<bool> {
        let n: i64 = sqlx::query_scalar("SELECT COUNT(*) FROM galleries WHERE id = ?")
            .bind(id)
            .fetch_one(db)
            .await?;
        Ok(n > 0)
    }

    pub async fn count(db: &Db) -> sqlx::Result<i64> {
        sqlx::query_scalar("SELECT COUNT(*) FROM galleries")
            .fetch_one(db)
            .await
    }

    pub async fn all_by_title(db: &Db) -> sqlx::Result<Vec<Gallery>> {
        sqlx::query_as("SELECT * FROM galleries ORDER BY title ASC")
            .fetch_all(db)
            .await
    }

    pub async fn images(db: &Db, gallery_id: i64) -> sqlx::Result<Vec<GalleryImage>> {
        sqlx::query_as("SELECT id, gallery_id, image_path, caption, alt_text, sort_order FROM gallery_images WHERE gallery_id = ? ORDER BY sort_order ASC, id ASC")
            .bind(gallery_id)
            .fetch_all(db)
            .await
    }

    pub async fn with_images(self, db: &Db) -> sqlx::Result<GalleryWithImages> {
        let images = Self::images(db, self.id).await?;
        Ok(GalleryWithImages {
            gallery: self,
            images,
        })
    }

    /// `Gallery::withCount('images')->latest()`.
    pub async fn all_with_counts(db: &Db) -> sqlx::Result<Vec<GalleryWithCount>> {
        sqlx::query_as(
            "SELECT galleries.*, (SELECT COUNT(*) FROM gallery_images WHERE gallery_images.gallery_id = galleries.id) AS images_count
             FROM galleries ORDER BY created_at DESC, id DESC",
        )
        .fetch_all(db)
        .await
    }

    pub async fn paginate_with_counts(
        db: &Db,
        per_page: i64,
        page: i64,
    ) -> sqlx::Result<(Vec<GalleryWithCount>, i64)> {
        let total = Self::count(db).await?;
        let rows = sqlx::query_as(
            "SELECT galleries.*, (SELECT COUNT(*) FROM gallery_images WHERE gallery_images.gallery_id = galleries.id) AS images_count
             FROM galleries ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?",
        )
        .bind(per_page)
        .bind((page - 1) * per_page)
        .fetch_all(db)
        .await?;
        Ok((rows, total))
    }

    pub async fn create(db: &Db, input: &GalleryInput) -> sqlx::Result<i64> {
        let ts = to_db(now());
        let mut tx = db.begin().await?;
        let id = sqlx::query("INSERT INTO galleries (title, slug, description, cover_image, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)")
            .bind(&input.title)
            .bind(&input.slug)
            .bind(&input.description)
            .bind(&input.cover_image)
            .bind(&ts)
            .bind(&ts)
            .execute(&mut *tx)
            .await?
            .last_insert_rowid();
        for (index, image) in input.images.iter().enumerate() {
            sqlx::query("INSERT INTO gallery_images (gallery_id, image_path, caption, alt_text, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)")
                .bind(id)
                .bind(&image.path)
                .bind(&image.caption)
                .bind(&image.alt_text)
                .bind(index as i64)
                .bind(&ts)
                .bind(&ts)
                .execute(&mut *tx)
                .await?;
        }
        tx.commit().await?;
        Ok(id)
    }

    /// Update the gallery and replace its images (the slug is never changed).
    pub async fn update(db: &Db, id: i64, input: &GalleryInput) -> sqlx::Result<()> {
        let ts = to_db(now());
        let mut tx = db.begin().await?;
        sqlx::query("UPDATE galleries SET title = ?, description = ?, cover_image = ?, updated_at = ? WHERE id = ?")
            .bind(&input.title)
            .bind(&input.description)
            .bind(&input.cover_image)
            .bind(&ts)
            .bind(id)
            .execute(&mut *tx)
            .await?;
        sqlx::query("DELETE FROM gallery_images WHERE gallery_id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        for (index, image) in input.images.iter().enumerate() {
            sqlx::query("INSERT INTO gallery_images (gallery_id, image_path, caption, alt_text, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)")
                .bind(id)
                .bind(&image.path)
                .bind(&image.caption)
                .bind(&image.alt_text)
                .bind(index as i64)
                .bind(&ts)
                .bind(&ts)
                .execute(&mut *tx)
                .await?;
        }
        tx.commit().await
    }

    pub async fn delete(db: &Db, id: i64) -> sqlx::Result<()> {
        let mut tx = db.begin().await?;
        sqlx::query("DELETE FROM gallery_images WHERE gallery_id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        sqlx::query("UPDATE posts SET gallery_id = NULL WHERE gallery_id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        sqlx::query("DELETE FROM galleries WHERE id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        tx.commit().await
    }
}
