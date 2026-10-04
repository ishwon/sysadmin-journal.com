//! String helpers that mirror the Laravel/PHP helpers the original app relied on.

use std::sync::LazyLock;

use rand::Rng;
use regex::Regex;

static TAG_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r"(?s)<!--.*?-->|<[^>]*>").expect("valid regex"));
static WORD_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r"[A-Za-z][A-Za-z'\-]*").expect("valid regex"));

/// `Str::slug()`.
pub fn slug(value: &str) -> String {
    slug::slugify(value)
}

/// `strip_tags()`.
pub fn strip_tags(html: &str) -> String {
    TAG_RE.replace_all(html, "").into_owned()
}

/// `Str::limit($value, $limit)` with the default `...` ending.
pub fn limit(value: &str, limit: usize) -> String {
    let count = value.chars().count();
    if count <= limit {
        return value.to_string();
    }
    let truncated: String = value.chars().take(limit).collect();
    format!("{}...", truncated.trim_end())
}

/// `str_word_count()`.
pub fn word_count(text: &str) -> usize {
    WORD_RE.find_iter(text).count()
}

/// `Post::calculateReadingTime()`: minutes at 200 wpm, never below one.
pub fn reading_time(text: Option<&str>) -> i64 {
    match text {
        None | Some("") => 1,
        Some(text) => ((word_count(text) as f64 / 200.0).round() as i64).max(1),
    }
}

/// `Str::plural($word, $count)` for the handful of words the views use.
pub fn plural(word: &str, count: i64) -> String {
    if count == 1 {
        word.to_string()
    } else {
        format!("{word}s")
    }
}

/// Uppercase initials (`Str::upper(Str::substr($name, 0, 2))`).
pub fn initials(name: &str) -> String {
    name.chars().take(2).collect::<String>().to_uppercase()
}

/// `Str::random($length)`: alphanumeric.
pub fn random(length: usize) -> String {
    const CHARS: &[u8] = b"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let mut rng = rand::rng();
    (0..length)
        .map(|_| CHARS[rng.random_range(0..CHARS.len())] as char)
        .collect()
}

/// `e()` / `htmlspecialchars()`.
pub fn escape(value: &str) -> String {
    html_escape::encode_double_quoted_attribute(value).into_owned()
}

/// `htmlspecialchars($value, ENT_XML1)`.
pub fn escape_xml(value: &str) -> String {
    let mut out = String::with_capacity(value.len());
    for c in value.chars() {
        match c {
            '&' => out.push_str("&amp;"),
            '<' => out.push_str("&lt;"),
            '>' => out.push_str("&gt;"),
            '"' => out.push_str("&quot;"),
            '\'' => out.push_str("&#39;"),
            _ => out.push(c),
        }
    }
    out
}

/// `filled()`: true when the value is present and not just whitespace.
pub fn filled(value: Option<&str>) -> bool {
    value.map(|v| !v.trim().is_empty()).unwrap_or(false)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn strips_tags() {
        assert_eq!(strip_tags("<p>Hello <b>world</b></p>"), "Hello world");
    }

    #[test]
    fn counts_words() {
        assert_eq!(word_count("Hello, world! It's 3 o'clock"), 4);
    }

    #[test]
    fn limits_text() {
        assert_eq!(limit("hello", 10), "hello");
        assert_eq!(limit("hello world", 5), "hello...");
    }
}
