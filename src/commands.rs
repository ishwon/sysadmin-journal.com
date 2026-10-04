//! The console commands that used to be Artisan commands.

use std::path::Path;

use anyhow::{Context, Result, bail};
use serde_json::Value;

use crate::db::Db;
use crate::models::post::{Post, PostInput, PostQuery};
use crate::models::tag::{Tag, TagInput};
use crate::models::user::{User, UserInput};
use crate::support::{dates, markdown, text};

fn s(v: &Value, key: &str) -> Option<String> {
    v.get(key).and_then(|x| x.as_str()).map(|x| x.to_string())
}

fn replace_ghost_url(value: Option<String>) -> Option<String> {
    value.map(|v| v.replace("__GHOST_URL__", ""))
}

fn parse_dt(value: Option<String>) -> Option<chrono::NaiveDateTime> {
    value.and_then(|v| dates::parse(&v))
}

/// `ghost:import {path?}` — import posts, pages, tags and users from a Ghost JSON export.
pub async fn ghost_import(db: &Db, path: Option<String>, bcrypt_rounds: u32) -> Result<()> {
    let path = match path {
        Some(p) => p,
        None => find_export_file().context("Ghost export file not found.")?,
    };
    if !Path::new(&path).is_file() {
        bail!("Ghost export file not found.");
    }
    println!("Importing from: {path}");

    let json: Value = serde_json::from_str(&std::fs::read_to_string(&path)?)?;
    let data = json
        .get("db")
        .and_then(|d| d.get(0))
        .and_then(|d| d.get("data"))
        .context("Unexpected export format: missing db[0].data")?;

    let empty = Vec::new();
    let list = |key: &str| data.get(key).and_then(|v| v.as_array()).unwrap_or(&empty);
    let posts_meta: std::collections::HashMap<String, &Value> = list("posts_meta")
        .iter()
        .filter_map(|m| s(m, "post_id").map(|id| (id, m)))
        .collect();

    let mut user_map = std::collections::HashMap::new();
    println!("Importing users...");
    let password = bcrypt::hash("changeme123", bcrypt_rounds)?;
    for user in list("users") {
        let id = User::create(
            db,
            &UserInput {
                name: s(user, "name").unwrap_or_default(),
                slug: s(user, "slug").unwrap_or_default(),
                email: s(user, "email").unwrap_or_default(),
                password: Some(password.clone()),
                bio: s(user, "bio"),
                profile_image: replace_ghost_url(s(user, "profile_image")),
                website: s(user, "website"),
                location: s(user, "location"),
                facebook: s(user, "facebook"),
                twitter: s(user, "twitter"),
                ghost_id: s(user, "id"),
            },
        )
        .await?;
        if let Some(ghost_id) = s(user, "id") {
            user_map.insert(ghost_id, id);
        }
    }
    println!("  Imported {} users.", user_map.len());

    let mut tag_map = std::collections::HashMap::new();
    println!("Importing tags...");
    for tag in list("tags") {
        let id = Tag::create(
            db,
            &TagInput {
                name: s(tag, "name").unwrap_or_default(),
                slug: s(tag, "slug").unwrap_or_default(),
                description: s(tag, "description"),
                feature_image: replace_ghost_url(s(tag, "feature_image")),
                meta_title: s(tag, "meta_title"),
                meta_description: s(tag, "meta_description"),
                ghost_id: s(tag, "id"),
            },
        )
        .await?;
        if let Some(ghost_id) = s(tag, "id") {
            tag_map.insert(ghost_id, id);
        }
    }
    println!("  Imported {} tags.", tag_map.len());

    let mut post_map = std::collections::HashMap::new();
    println!("Importing posts and pages...");
    for post in list("posts") {
        let meta = s(post, "id").and_then(|id| posts_meta.get(&id).copied());
        let m = |key: &str| meta.and_then(|m| s(m, key));
        let plaintext = s(post, "plaintext");
        let id = Post::create(
            db,
            &PostInput {
                title: s(post, "title").unwrap_or_default(),
                slug: s(post, "slug").unwrap_or_default(),
                html: replace_ghost_url(s(post, "html")),
                reading_time: text::reading_time(plaintext.as_deref()),
                plaintext,
                markdown: None,
                custom_excerpt: s(post, "custom_excerpt"),
                feature_image: replace_ghost_url(s(post, "feature_image")),
                feature_image_alt: m("feature_image_alt"),
                feature_image_caption: m("feature_image_caption"),
                kind: s(post, "type").unwrap_or_else(|| "post".into()),
                status: s(post, "status").unwrap_or_else(|| "draft".into()),
                published_at: parse_dt(s(post, "published_at")),
                meta_title: m("meta_title"),
                meta_description: m("meta_description"),
                og_image: replace_ghost_url(m("og_image")),
                og_title: m("og_title"),
                og_description: m("og_description"),
                twitter_image: replace_ghost_url(m("twitter_image")),
                twitter_title: m("twitter_title"),
                twitter_description: m("twitter_description"),
                canonical_url: s(post, "canonical_url"),
                ghost_id: s(post, "id"),
                gallery_id: None,
                created_at: parse_dt(s(post, "created_at")),
                updated_at: parse_dt(s(post, "updated_at")),
            },
        )
        .await?;
        if let Some(ghost_id) = s(post, "id") {
            post_map.insert(ghost_id, id);
        }
    }
    println!("  Imported {} posts/pages.", post_map.len());

    println!("Importing post-tag relationships...");
    let mut count = 0;
    for pt in list("posts_tags") {
        let post_id = s(pt, "post_id").and_then(|id| post_map.get(&id).copied());
        let tag_id = s(pt, "tag_id").and_then(|id| tag_map.get(&id).copied());
        if let (Some(post_id), Some(tag_id)) = (post_id, tag_id) {
            Post::attach_tag(
                db,
                post_id,
                tag_id,
                pt.get("sort_order").and_then(|v| v.as_i64()).unwrap_or(0),
            )
            .await?;
            count += 1;
        }
    }
    println!("  Imported {count} post-tag relationships.");

    println!("Importing post-author relationships...");
    let mut count = 0;
    for pa in list("posts_authors") {
        let post_id = s(pa, "post_id").and_then(|id| post_map.get(&id).copied());
        let user_id = s(pa, "author_id").and_then(|id| user_map.get(&id).copied());
        if let (Some(post_id), Some(user_id)) = (post_id, user_id) {
            Post::attach_author(
                db,
                post_id,
                user_id,
                pa.get("sort_order").and_then(|v| v.as_i64()).unwrap_or(0),
            )
            .await?;
            count += 1;
        }
    }
    println!("  Imported {count} post-author relationships.");

    println!("\nImport complete!");
    println!("{:<8} {:>6}", "Type", "Count");
    println!("{:<8} {:>6}", "Users", User::count(db).await?);
    println!("{:<8} {:>6}", "Tags", Tag::count(db).await?);
    println!(
        "{:<8} {:>6}",
        "Posts",
        PostQuery::new().posts().count(db).await?
    );
    println!(
        "{:<8} {:>6}",
        "Pages",
        PostQuery::new().pages().count(db).await?
    );
    Ok(())
}

fn find_export_file() -> Option<String> {
    let mut files: Vec<_> = std::fs::read_dir("ghost-nice")
        .ok()?
        .filter_map(|e| e.ok())
        .map(|e| e.path())
        .filter(|p| {
            let name = p.file_name().and_then(|n| n.to_str()).unwrap_or("");
            name.starts_with("sysadmin-journal.ghost.") && name.ends_with(".json")
        })
        .collect();
    files.sort();
    files.first().map(|p| p.to_string_lossy().to_string())
}

/// `posts:wrap-figures {--dry-run}` — turn titled images in saved HTML into figures.
pub async fn wrap_figures(db: &Db, dry_run: bool) -> Result<usize> {
    let posts: Vec<Post> = sqlx::query_as(
        "SELECT * FROM posts WHERE html IS NOT NULL AND html LIKE '%title=%' ORDER BY id",
    )
    .fetch_all(db)
    .await?;
    let mut count = 0;
    for post in posts {
        let Some(html) = post.html.as_deref() else {
            continue;
        };
        let wrapped = markdown::wrap_figures(html);
        if wrapped == html {
            continue;
        }
        println!("{} #{} {}", post.kind, post.id, post.slug);
        if !dry_run {
            Post::update_html(db, post.id, &wrapped, &text::strip_tags(&wrapped)).await?;
        }
        count += 1;
    }
    println!(
        "{}{count} {}.",
        if dry_run { "Would update " } else { "Updated " },
        text::plural("post", count as i64)
    );
    Ok(count)
}

/// `user:create` — create an account (replaces the database seeder / tinker).
pub async fn user_create(
    db: &Db,
    name: String,
    email: String,
    password: String,
    slug: Option<String>,
    bcrypt_rounds: u32,
) -> Result<i64> {
    let slug = slug.unwrap_or_else(|| text::slug(&name));
    if User::email_taken(db, &email, None).await? {
        bail!("A user with that email already exists.");
    }
    if User::slug_taken(db, &slug, None).await? {
        bail!("A user with slug {slug:?} already exists.");
    }
    let id = User::create(
        db,
        &UserInput {
            name,
            slug,
            email,
            password: Some(bcrypt::hash(password, bcrypt_rounds)?),
            ..Default::default()
        },
    )
    .await?;
    println!("Created user #{id}.");
    Ok(id)
}
