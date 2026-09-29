use std::str::FromStr;

use anyhow::Result;
use sqlx::SqlitePool;
use sqlx::sqlite::{SqliteConnectOptions, SqliteJournalMode, SqlitePoolOptions};

pub type Db = SqlitePool;

pub async fn connect(database_url: &str) -> Result<Db> {
    let options = SqliteConnectOptions::from_str(database_url)?
        .create_if_missing(true)
        .journal_mode(SqliteJournalMode::Wal)
        .foreign_keys(true);

    let pool = SqlitePoolOptions::new()
        .max_connections(8)
        .connect_with(options)
        .await?;
    Ok(pool)
}

pub async fn migrate(pool: &Db) -> Result<()> {
    sqlx::migrate!("./migrations").run(pool).await?;
    Ok(())
}
