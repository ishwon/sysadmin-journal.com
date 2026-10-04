//! Access to `application/x-www-form-urlencoded` bodies with PHP-style
//! bracket keys (`tags[]`, `images[0][path]`).

use std::collections::BTreeMap;

use axum::extract::rejection::FormRejection;
use axum::extract::{FromRequest, Request};
use serde_json::{Map, Value, json};

#[derive(Clone, Debug, Default)]
pub struct FormData {
    pairs: Vec<(String, String)>,
}

impl<S: Send + Sync> FromRequest<S> for FormData {
    type Rejection = FormRejection;

    async fn from_request(req: Request, state: &S) -> Result<Self, Self::Rejection> {
        let axum::Form(pairs) =
            axum::Form::<Vec<(String, String)>>::from_request(req, state).await?;
        Ok(Self { pairs })
    }
}

impl FormData {
    pub fn from_pairs(pairs: Vec<(String, String)>) -> Self {
        Self { pairs }
    }

    /// First value for a key, `None` when absent.
    pub fn get(&self, key: &str) -> Option<&str> {
        self.pairs
            .iter()
            .find(|(k, _)| k == key)
            .map(|(_, v)| v.as_str())
    }

    /// First value, empty string when absent.
    pub fn str(&self, key: &str) -> String {
        self.get(key).unwrap_or("").to_string()
    }

    /// First value trimmed to `None` when empty (`nullable` fields).
    pub fn opt(&self, key: &str) -> Option<String> {
        self.get(key)
            .filter(|v| !v.is_empty())
            .map(|v| v.to_string())
    }

    /// All values for `key` or `key[]`.
    pub fn list(&self, key: &str) -> Vec<String> {
        let bracket = format!("{key}[]");
        self.pairs
            .iter()
            .filter(|(k, _)| k == key || *k == bracket)
            .map(|(_, v)| v.clone())
            .collect()
    }

    /// `$request->boolean($key)`.
    pub fn boolean(&self, key: &str) -> bool {
        matches!(
            self.get(key),
            Some("1") | Some("true") | Some("on") | Some("yes")
        )
    }

    /// Rows of `key[i][field]` inputs, in index order.
    pub fn rows(&self, key: &str) -> Vec<BTreeMap<String, String>> {
        let prefix = format!("{key}[");
        let mut rows: BTreeMap<usize, BTreeMap<String, String>> = BTreeMap::new();
        for (k, v) in &self.pairs {
            let Some(rest) = k.strip_prefix(&prefix) else {
                continue;
            };
            let Some((index, field)) = rest.split_once("][") else {
                continue;
            };
            let Ok(index) = index.parse::<usize>() else {
                continue;
            };
            let field = field.trim_end_matches(']').to_string();
            rows.entry(index).or_default().insert(field, v.clone());
        }
        rows.into_values().collect()
    }

    /// Nested JSON for `old()`: `tags[]` → list, `images[0][path]` → list of objects.
    pub fn to_old(&self) -> BTreeMap<String, Value> {
        let mut root = Map::new();
        for (k, v) in &self.pairs {
            if k == "_token" || k == "_method" || k == "password" {
                continue;
            }
            insert_bracketed(&mut root, k, v);
        }
        root.into_iter().collect()
    }
}

fn insert_bracketed(root: &mut Map<String, Value>, key: &str, value: &str) {
    let Some(open) = key.find('[') else {
        root.insert(key.to_string(), json!(value));
        return;
    };
    let head = &key[..open];
    let segments: Vec<&str> = key[open..]
        .split('[')
        .filter(|s| !s.is_empty())
        .map(|s| s.trim_end_matches(']'))
        .collect();

    let mut node = root.entry(head.to_string()).or_insert(Value::Null);
    for (i, seg) in segments.iter().enumerate() {
        let last = i + 1 == segments.len();
        if seg.is_empty() || seg.parse::<usize>().is_ok() {
            if !node.is_array() {
                *node = Value::Array(Vec::new());
            }
            let arr = node.as_array_mut().expect("array");
            let index = seg.parse::<usize>().unwrap_or(arr.len());
            while arr.len() <= index {
                arr.push(Value::Null);
            }
            if last {
                arr[index] = json!(value);
                return;
            }
            node = &mut arr[index];
        } else {
            if !node.is_object() {
                *node = Value::Object(Map::new());
            }
            let obj = node.as_object_mut().expect("object");
            if last {
                obj.insert(seg.to_string(), json!(value));
                return;
            }
            node = obj.entry(seg.to_string()).or_insert(Value::Null);
        }
    }
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn parses_bracket_keys() {
        let form = FormData::from_pairs(vec![
            ("title".into(), "T".into()),
            ("tags[]".into(), "1".into()),
            ("tags[]".into(), "2".into()),
            ("images[0][path]".into(), "/a.jpg".into()),
            ("images[0][caption]".into(), "A".into()),
            ("images[1][path]".into(), "/b.jpg".into()),
        ]);
        assert_eq!(form.list("tags"), vec!["1", "2"]);
        let rows = form.rows("images");
        assert_eq!(rows.len(), 2);
        assert_eq!(rows[0]["caption"], "A");
        let old = form.to_old();
        assert_eq!(old["tags"], json!(["1", "2"]));
        assert_eq!(old["images"][1]["path"], json!("/b.jpg"));
    }
}
