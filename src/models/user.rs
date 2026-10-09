use chrono::NaiveDateTime;
use serde::{Deserialize, Serialize};
use sqlx::FromRow;

use crate::db::Db;
use crate::support::dates::{self, now, to_db};

#[derive(Clone, Debug, FromRow, Serialize, Deserialize)]
pub struct User {
    pub id: i64,
    pub name: String,
    pub slug: String,
    pub email: String,
    #[serde(with = "super::db_datetime")]
    pub email_verified_at: Option<NaiveDateTime>,
    #[serde(skip_serializing)]
    pub password: String,
    pub bio: Option<String>,
    pub profile_image: Option<String>,
    pub website: Option<String>,
    pub location: Option<String>,
    pub facebook: Option<String>,
    pub twitter: Option<String>,
    pub ghost_id: Option<String>,
    #[serde(skip_serializing)]
    pub remember_token: Option<String>,
    #[serde(with = "super::db_datetime")]
    pub created_at: Option<NaiveDateTime>,
    #[serde(with = "super::db_datetime")]
    pub updated_at: Option<NaiveDateTime>,
}

/// A user row plus its post count, for the dashboard listing.
#[derive(Clone, Debug, FromRow, Serialize)]
pub struct UserWithCount {
    #[sqlx(flatten)]
    #[serde(flatten)]
    pub user: User,
    pub posts_count: i64,
}

impl std::ops::Deref for UserWithCount {
    type Target = User;

    fn deref(&self) -> &User {
        &self.user
    }
}

/// Fields written by the dashboard form and the Ghost importer.
#[derive(Clone, Debug, Default)]
pub struct UserInput {
    pub name: String,
    pub slug: String,
    pub email: String,
    /// Already hashed.
    pub password: Option<String>,
    pub bio: Option<String>,
    pub profile_image: Option<String>,
    pub website: Option<String>,
    pub location: Option<String>,
    pub facebook: Option<String>,
    pub twitter: Option<String>,
    pub ghost_id: Option<String>,
}

impl User {
    pub async fn find(db: &Db, id: i64) -> sqlx::Result<Option<User>> {
        sqlx::query_as("SELECT * FROM users WHERE id = ?")
            .bind(id)
            .fetch_optional(db)
            .await
    }

    pub async fn find_by_email(db: &Db, email: &str) -> sqlx::Result<Option<User>> {
        sqlx::query_as("SELECT * FROM users WHERE email = ?")
            .bind(email)
            .fetch_optional(db)
            .await
    }

    pub async fn find_by_slug(db: &Db, slug: &str) -> sqlx::Result<Option<User>> {
        sqlx::query_as("SELECT * FROM users WHERE slug = ?")
            .bind(slug)
            .fetch_optional(db)
            .await
    }

    pub async fn count(db: &Db) -> sqlx::Result<i64> {
        sqlx::query_scalar("SELECT COUNT(*) FROM users")
            .fetch_one(db)
            .await
    }

    pub async fn slug_taken(db: &Db, slug: &str, exclude_id: Option<i64>) -> sqlx::Result<bool> {
        let n: i64 = sqlx::query_scalar("SELECT COUNT(*) FROM users WHERE slug = ? AND id != ?")
            .bind(slug)
            .bind(exclude_id.unwrap_or(0))
            .fetch_one(db)
            .await?;
        Ok(n > 0)
    }

    pub async fn email_taken(db: &Db, email: &str, exclude_id: Option<i64>) -> sqlx::Result<bool> {
        let n: i64 = sqlx::query_scalar("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?")
            .bind(email)
            .bind(exclude_id.unwrap_or(0))
            .fetch_one(db)
            .await?;
        Ok(n > 0)
    }

    /// `User::withCount('posts')->orderBy('name')` (one page).
    pub async fn paginate_with_counts(
        db: &Db,
        per_page: i64,
        page: i64,
    ) -> sqlx::Result<(Vec<UserWithCount>, i64)> {
        let total = Self::count(db).await?;
        let rows = sqlx::query_as(
            "SELECT users.*, (SELECT COUNT(*) FROM post_user WHERE post_user.user_id = users.id) AS posts_count
             FROM users ORDER BY name ASC LIMIT ? OFFSET ?",
        )
        .bind(per_page)
        .bind((page - 1) * per_page)
        .fetch_all(db)
        .await?;
        Ok((rows, total))
    }

    /// `User::has('posts')->get()`.
    pub async fn with_posts(db: &Db) -> sqlx::Result<Vec<User>> {
        sqlx::query_as("SELECT * FROM users WHERE EXISTS (SELECT 1 FROM post_user WHERE post_user.user_id = users.id) ORDER BY id")
            .fetch_all(db)
            .await
    }

    pub async fn create(db: &Db, input: &UserInput) -> sqlx::Result<i64> {
        let ts = to_db(now());
        let id = sqlx::query(
            "INSERT INTO users (name, slug, email, password, bio, profile_image, website, location, facebook, twitter, ghost_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        )
        .bind(&input.name)
        .bind(&input.slug)
        .bind(&input.email)
        .bind(input.password.as_deref().unwrap_or_default())
        .bind(&input.bio)
        .bind(&input.profile_image)
        .bind(&input.website)
        .bind(&input.location)
        .bind(&input.facebook)
        .bind(&input.twitter)
        .bind(&input.ghost_id)
        .bind(&ts)
        .bind(&ts)
        .execute(db)
        .await?
        .last_insert_rowid();
        Ok(id)
    }

    pub async fn update(db: &Db, id: i64, input: &UserInput) -> sqlx::Result<()> {
        sqlx::query(
            "UPDATE users SET name = ?, slug = ?, email = ?, bio = ?, profile_image = ?, website = ?, location = ?, facebook = ?, twitter = ?,
             password = COALESCE(?, password), updated_at = ? WHERE id = ?",
        )
        .bind(&input.name)
        .bind(&input.slug)
        .bind(&input.email)
        .bind(&input.bio)
        .bind(&input.profile_image)
        .bind(&input.website)
        .bind(&input.location)
        .bind(&input.facebook)
        .bind(&input.twitter)
        .bind(&input.password)
        .bind(to_db(now()))
        .bind(id)
        .execute(db)
        .await?;
        Ok(())
    }

    pub async fn set_remember_token(db: &Db, id: i64, token: Option<&str>) -> sqlx::Result<()> {
        sqlx::query("UPDATE users SET remember_token = ? WHERE id = ?")
            .bind(token)
            .bind(id)
            .execute(db)
            .await?;
        Ok(())
    }

    /// Detach the user's posts and delete the account.
    pub async fn delete(db: &Db, id: i64) -> sqlx::Result<()> {
        let mut tx = db.begin().await?;
        sqlx::query("DELETE FROM post_user WHERE user_id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        sqlx::query("DELETE FROM users WHERE id = ?")
            .bind(id)
            .execute(&mut *tx)
            .await?;
        tx.commit().await
    }

    #[allow(dead_code)]
    pub fn updated_at_iso(&self) -> String {
        dates::iso8601(self.updated_at.unwrap_or_else(now))
    }
}
