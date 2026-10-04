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

    // Say which file is used: a mistyped DATABASE_URL otherwise silently
    // creates an empty database somewhere else.
    let path = options.get_filename().to_path_buf();
    if path.exists() {
        let absolute = std::fs::canonicalize(&path).unwrap_or(path);
        let bytes = std::fs::metadata(&absolute).map(|m| m.len()).unwrap_or(0);
        tracing::info!("database: {} ({bytes} bytes)", absolute.display());
    } else {
        tracing::warn!(
            "database {} does not exist and will be created empty (cwd: {})",
            path.display(),
            std::env::current_dir()
                .map(|d| d.display().to_string())
                .unwrap_or_default()
        );
    }

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
