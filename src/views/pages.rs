//! One struct per page. Templates reference the fields by name and the
//! helpers imported here.

#![allow(unused_imports)]

use sailfish::TemplateSimple;

use super::ui;
use super::{
    Base, date, date_at, date_or, esc, initials, js, kb, method_field, pad2, plural, s, slug,
    urlencode,
};
use crate::handlers::dashboard::media::MediaFile;
use crate::models::gallery::{Gallery, GalleryImageInput, GalleryWithCount, GalleryWithImages};
use crate::models::post::{Post, PostView};
use crate::models::tag::{Tag, TagWithCount};
use crate::models::user::{User, UserWithCount};
use crate::support::pagination::Page;

// ---- public site -------------------------------------------------------------

#[derive(TemplateSimple)]
#[template(path = "posts/index.stpl")]
pub struct PostsIndex {
    pub featured: Option<PostView>,
    pub posts: Page<PostView>,
}

#[derive(TemplateSimple)]
#[template(path = "posts/show.stpl")]
pub struct PostShow {
    pub post: PostView,
    pub previous: Option<Post>,
    pub next: Option<Post>,
}

#[derive(TemplateSimple)]
#[template(path = "pages/show.stpl")]
pub struct PageShow {
    pub post: PostView,
}

#[derive(TemplateSimple)]
#[template(path = "tags/show.stpl")]
pub struct TagShow {
    pub tag: Tag,
    pub posts: Page<PostView>,
}

#[derive(TemplateSimple)]
#[template(path = "authors/show.stpl")]
pub struct AuthorShow {
    pub author: User,
    pub posts: Page<PostView>,
}

#[derive(TemplateSimple)]
#[template(path = "galleries/index.stpl")]
pub struct GalleriesIndex {
    pub galleries: Vec<GalleryWithCount>,
}

#[derive(TemplateSimple)]
#[template(path = "galleries/show.stpl")]
pub struct GalleryShow {
    pub gallery: GalleryWithImages,
}

#[derive(TemplateSimple)]
#[template(path = "brand-system.stpl")]
pub struct BrandSystem;

#[derive(TemplateSimple)]
#[template(path = "auth/login.stpl")]
pub struct Login<'a> {
    pub base: &'a Base,
}

#[derive(TemplateSimple)]
#[template(path = "feed/rss.stpl")]
pub struct Rss<'a> {
    pub base: &'a Base,
    pub title: String,
    pub description: String,
    pub link: String,
    pub self_url: String,
    pub last_build_date: String,
    pub posts: Vec<PostView>,
}

// ---- dashboard ---------------------------------------------------------------

#[derive(TemplateSimple)]
#[template(path = "dashboard/index.stpl")]
pub struct DashboardIndex<'a> {
    pub base: &'a Base,
    pub post_count: i64,
    pub published_count: i64,
    pub page_count: i64,
    pub gallery_count: i64,
    pub tag_count: i64,
    pub user_count: i64,
    pub sitemap_exists: bool,
    pub recent_posts: Vec<PostView>,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/posts/index.stpl")]
pub struct PostsAdmin<'a> {
    pub base: &'a Base,
    pub posts: Page<PostView>,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/posts/form.stpl")]
pub struct PostForm<'a> {
    pub base: &'a Base,
    pub post: Option<PostView>,
    pub post_tag_ids: Vec<i64>,
    pub tags: Vec<Tag>,
    pub galleries: Vec<Gallery>,
    pub action: String,
    pub editing: bool,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/posts/preview.stpl")]
pub struct PostPreview<'a> {
    pub base: &'a Base,
    pub post: PostView,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/pages/index.stpl")]
pub struct PagesAdmin<'a> {
    pub base: &'a Base,
    pub pages: Page<Post>,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/pages/form.stpl")]
pub struct PageForm<'a> {
    pub base: &'a Base,
    pub page: Option<Post>,
    pub action: String,
    pub editing: bool,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/galleries/index.stpl")]
pub struct GalleriesAdmin<'a> {
    pub base: &'a Base,
    pub galleries: Page<GalleryWithCount>,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/galleries/form.stpl")]
pub struct GalleryForm<'a> {
    pub base: &'a Base,
    pub gallery: Option<GalleryWithImages>,
    pub images_initial: Vec<GalleryImageInput>,
    pub action: String,
    pub editing: bool,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/tags/index.stpl")]
pub struct TagsAdmin<'a> {
    pub base: &'a Base,
    pub tags: Page<TagWithCount>,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/tags/form.stpl")]
pub struct TagForm<'a> {
    pub base: &'a Base,
    pub tag: Option<Tag>,
    pub action: String,
    pub editing: bool,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/users/index.stpl")]
pub struct UsersAdmin<'a> {
    pub base: &'a Base,
    pub users: Page<UserWithCount>,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/users/form.stpl")]
pub struct UserForm<'a> {
    pub base: &'a Base,
    pub user: Option<User>,
    pub action: String,
    pub editing: bool,
}

#[derive(TemplateSimple)]
#[template(path = "dashboard/media/index.stpl")]
pub struct MediaIndex<'a> {
    pub base: &'a Base,
    pub current_media_path: String,
    pub directories: Vec<String>,
    pub images: Vec<MediaFile>,
    pub pdfs: Vec<MediaFile>,
}
