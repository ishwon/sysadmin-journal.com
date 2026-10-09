use std::sync::Arc;

use crate::config::Config;
use crate::db::Db;
use crate::support::vite::Vite;

#[derive(Clone)]
pub struct AppState {
    pub db: Db,
    pub config: Arc<Config>,
    pub vite: Arc<Vite>,
    pub cookie_key: tower_cookies::Key,
}

impl AppState {
    pub fn new(db: Db, config: Config) -> Self {
        let vite = Arc::new(Vite::load(&config.public_dir));
        let cookie_key = tower_cookies::Key::derive_from(&config.key);
        Self {
            db,
            config: Arc::new(config),
            vite,
            cookie_key,
        }
    }
}
