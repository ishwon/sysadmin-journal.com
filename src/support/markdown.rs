//! Markdown → HTML for posts and pages.
//!
//! CommonMark with one convention: an image with a title
//!   `![alt](/path.jpg "A caption")`
//! becomes a `<figure>` with a `<figcaption>`, so the caption renders in the
//! mono register directly under the image (see `.prose-brand figcaption`).

use std::sync::LazyLock;

use pulldown_cmark::{Options, Parser, html};
use regex::Regex;

static FIGURE_RE: LazyLock<Regex> = LazyLock::new(|| {
    Regex::new(r#"(?is)<p>\s*(<img\b[^>]*\btitle="([^"]*)"[^>]*>)\s*</p>"#).expect("valid regex")
});

static TITLE_ATTR_RE: LazyLock<Regex> =
    LazyLock::new(|| Regex::new(r#"(?i)\s*title="[^"]*""#).expect("valid regex"));

/// Convert CommonMark to HTML. Returns `None` for empty input.
pub fn convert(content: Option<&str>) -> Option<String> {
    let content = content?;
    if content.is_empty() {
        return None;
    }

    let parser = Parser::new_ext(content, Options::empty());
    let mut out = String::with_capacity(content.len() * 3 / 2);
    html::push_html(&mut out, parser);

    Some(wrap_figures(&out))
}

/// Turn `<p><img … title="Caption"></p>` into a captioned `<figure>`.
pub fn wrap_figures(html: &str) -> String {
    FIGURE_RE
        .replace_all(html, |caps: &regex::Captures| {
            let img = TITLE_ATTR_RE.replace_all(&caps[1], "");
            format!(
                "<figure>{img}<figcaption>{}</figcaption></figure>",
                &caps[2]
            )
        })
        .into_owned()
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn titled_image_becomes_figure() {
        assert_eq!(
            convert(Some("![Alt](/a.jpg \"Cap\")")).unwrap(),
            "<figure><img src=\"/a.jpg\" alt=\"Alt\" /><figcaption>Cap</figcaption></figure>\n"
        );
    }

    #[test]
    fn untitled_image_stays_paragraph() {
        assert_eq!(
            convert(Some("![Alt](/a.jpg)")).unwrap(),
            "<p><img src=\"/a.jpg\" alt=\"Alt\" /></p>\n"
        );
    }
}
