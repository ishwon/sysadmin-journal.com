//! A length-aware paginator with the same public surface the Blade views used
//! (`currentPage`, `lastPage`, `hasPages`, `previousPageUrl`, `links`, …).

use serde::Serialize;

#[derive(Clone, Debug, Serialize)]
pub struct PageLink {
    pub label: String,
    pub url: Option<String>,
    pub active: bool,
}

#[derive(Clone, Debug, Serialize)]
pub struct Page<T: Serialize> {
    pub data: Vec<T>,
    pub total: i64,
    pub per_page: i64,
    pub current_page: i64,
    pub last_page: i64,
    pub has_pages: bool,
    pub on_first_page: bool,
    pub prev_url: Option<String>,
    pub next_url: Option<String>,
    pub first_item: Option<i64>,
    pub last_item: Option<i64>,
    pub links: Vec<PageLink>,
}

impl<T: Serialize> Page<T> {
    /// `path` is the request path; `query` are extra query parameters to keep
    /// on every page link (`withQueryString()`).
    pub fn new(
        data: Vec<T>,
        total: i64,
        per_page: i64,
        current_page: i64,
        path: &str,
        query: &[(String, String)],
    ) -> Self {
        let last_page = ((total as f64) / (per_page as f64)).ceil().max(1.0) as i64;
        let count = data.len() as i64;
        let url = |page: i64| -> String {
            let mut params: Vec<String> = query
                .iter()
                .filter(|(k, _)| k != "page")
                .map(|(k, v)| format!("{}={}", urlencode(k), urlencode(v)))
                .collect();
            params.push(format!("page={page}"));
            format!("{path}?{}", params.join("&"))
        };
        let prev_url = (current_page > 1).then(|| url(current_page - 1));
        let next_url = (current_page < last_page).then(|| url(current_page + 1));
        let has_pages = current_page != 1 || next_url.is_some();
        let first_item = (count > 0).then(|| (current_page - 1) * per_page + 1);
        let last_item = first_item.map(|f| f + count - 1);

        let links = window_links(current_page, last_page, &url);

        Self {
            data,
            total,
            per_page,
            current_page,
            last_page,
            has_pages,
            on_first_page: current_page <= 1,
            prev_url,
            next_url,
            first_item,
            last_item,
            links,
        }
    }
}

/// Laravel's URL window: all pages when few, otherwise first two, a window of
/// three around the current page and the last two, with `...` separators.
fn window_links(current: i64, last: i64, url: &dyn Fn(i64) -> String) -> Vec<PageLink> {
    let link = |p: i64| PageLink {
        label: p.to_string(),
        url: Some(url(p)),
        active: p == current,
    };
    let dots = || PageLink {
        label: "...".into(),
        url: None,
        active: false,
    };

    if last <= 10 {
        return (1..=last).map(link).collect();
    }

    let mut links = Vec::new();
    if current <= 5 {
        links.extend((1..=7).map(link));
        links.push(dots());
        links.extend((last - 1..=last).map(link));
    } else if current > last - 5 {
        links.extend((1..=2).map(link));
        links.push(dots());
        links.extend((last - 6..=last).map(link));
    } else {
        links.extend((1..=2).map(link));
        links.push(dots());
        links.extend((current - 1..=current + 1).map(link));
        links.push(dots());
        links.extend((last - 1..=last).map(link));
    }
    links
}

fn urlencode(value: &str) -> String {
    form_urlencoded::byte_serialize(value.as_bytes()).collect()
}

/// Parse the `page` query parameter the way Laravel does (>= 1, default 1).
pub fn page_from(value: Option<&str>) -> i64 {
    value
        .and_then(|v| v.parse::<i64>().ok())
        .filter(|p| *p >= 1)
        .unwrap_or(1)
}
