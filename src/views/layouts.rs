#![allow(unused_imports)]

use sailfish::TemplateSimple;

use super::ui;
use super::{Base, NavCounts, Seo, esc, initials};

/// Public site chrome (`layouts/app.blade.php`).
#[derive(TemplateSimple)]
#[template(path = "layouts/app.stpl")]
pub struct AppLayout<'a> {
    pub base: &'a Base,
    pub seo: &'a Seo,
    pub feed_url: Option<String>,
    pub content: String,
}

/// Backoffice chrome (`layouts/dashboard.blade.php`).
#[derive(TemplateSimple)]
#[template(path = "layouts/dashboard.stpl")]
pub struct DashboardLayout<'a> {
    pub base: &'a Base,
    pub title: String,
    pub page_title: Option<String>,
    pub page_aside: String,
    pub actions: String,
    pub breadcrumbs: Option<String>,
    pub nav_counts: NavCounts,
    pub content: String,
}

/// The sections a dashboard page fills in (`@section('title')` and friends).
#[derive(Default)]
pub struct DashboardPage {
    pub title: String,
    pub page_title: Option<String>,
    pub page_aside: String,
    pub actions: String,
    pub breadcrumbs: Option<String>,
    pub content: String,
}

impl DashboardPage {
    pub fn new(title: impl Into<String>, content: String) -> Self {
        Self {
            title: title.into(),
            content,
            ..Default::default()
        }
    }

    pub fn heading(mut self, page_title: impl Into<String>) -> Self {
        self.page_title = Some(page_title.into());
        self
    }

    pub fn aside(mut self, html: String) -> Self {
        self.page_aside = html;
        self
    }

    pub fn actions(mut self, html: String) -> Self {
        self.actions = html;
        self
    }

    pub fn breadcrumbs(mut self, html: String) -> Self {
        self.breadcrumbs = Some(html);
        self
    }
}

/// Brand system chrome (`layouts/brand-system.blade.php`).
#[derive(TemplateSimple)]
#[template(path = "layouts/brand-system.stpl")]
pub struct BrandLayout<'a> {
    pub base: &'a Base,
    pub content: String,
}
