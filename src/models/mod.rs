pub mod gallery;
pub mod post;
pub mod tag;
pub mod user;

use serde::Deserializer;
use serde::{Deserialize, Serialize};

/// Serialize an optional `NaiveDateTime` in the storage format so templates
/// can pass it straight to the `date` filter.
pub mod db_datetime {
    use chrono::NaiveDateTime;
    use serde::{Deserialize, Deserializer, Serialize, Serializer};

    pub fn serialize<S: Serializer>(
        value: &Option<NaiveDateTime>,
        s: S,
    ) -> Result<S::Ok, S::Error> {
        match value {
            Some(dt) => crate::support::dates::to_db(*dt).serialize(s),
            None => s.serialize_none(),
        }
    }

    pub fn deserialize<'de, D: Deserializer<'de>>(d: D) -> Result<Option<NaiveDateTime>, D::Error> {
        let raw: Option<String> = Option::deserialize(d)?;
        Ok(raw.and_then(|r| crate::support::dates::parse(&r)))
    }
}

/// Build `?, ?, ?` placeholders for an `IN (...)` clause.
pub fn placeholders(n: usize) -> String {
    std::iter::repeat_n("?", n).collect::<Vec<_>>().join(", ")
}

#[allow(dead_code)]
#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct Count {
    pub count: i64,
}

#[allow(dead_code)]
pub fn de_i64<'de, D: Deserializer<'de>>(d: D) -> Result<i64, D::Error> {
    i64::deserialize(d)
}
