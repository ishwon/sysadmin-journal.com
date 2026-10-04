use std::collections::BTreeMap;

use serde::{Deserialize, Serialize};

/// Data flashed to the session for exactly one following request:
/// `session('success')`, `session('error')`, `$errors` and `old()`.
#[derive(Clone, Debug, Default, Serialize, Deserialize)]
pub struct Flash {
    pub success: Option<String>,
    pub error: Option<String>,
    #[serde(default)]
    pub errors: Vec<String>,
    #[serde(default)]
    pub old: BTreeMap<String, serde_json::Value>,
}

impl Flash {
    pub const KEY: &'static str = "_flash";

    pub fn is_empty(&self) -> bool {
        self.success.is_none()
            && self.error.is_none()
            && self.errors.is_empty()
            && self.old.is_empty()
    }
}
