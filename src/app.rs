use std::sync::Arc;

use crate::config::Config;
use crate::db::Db;
use crate::support::vite::Vite;
use crate::templates::Templates;

#[derive(Clone)]
pub struct AppState {
    pub db: Db,
    pub config: Arc<Config>,
    pub templates: Templates,
    pub cookie_key: tower_cookies::Key,
}

impl AppState {
    pub fn new(db: Db, config: Config) -> Self {
        let vite = Vite::load(&config.public_dir);
        let templates = Templates::new(
            if config.is_local() {
                Some(config.templates_dir.as_path())
            } else {
                None
            },
            vite,
            &config.app_url,
        );
        let cookie_key = tower_cookies::Key::derive_from(&config.key);
        Self {
            db,
            config: Arc::new(config),
            templates,
            cookie_key,
        }
    }
}
