mod common;

use common::*;
use sysadmin_journal::models::post::Post;

#[tokio::test]
async fn serves_an_rss_feed_of_published_posts() {
    let mut app = TestApp::new().await;
    let author = create_user(&app.db, "Ish Sookun", "ish").await;
    let tag = create_tag(&app.db, "Linux", "linux").await;
    let mut input = published(post_input("Hello & welcome"));
    input.slug = "hello-welcome".into();
    input.html = Some("<p>Body</p><img src=\"/content/images/a.jpg\">[gallery:photos]".into());
    input.feature_image = Some("/content/images/cover.jpg".into());
    let post = create_post(&app.db, input).await;
    Post::attach_author(&app.db, post.id, author.id, 0)
        .await
        .unwrap();
    Post::attach_tag(&app.db, post.id, tag.id, 0).await.unwrap();

    let response = app.get("/rss").await;
    response.assert_ok();
    assert_eq!(
        response.header("content-type"),
        Some("application/xml; charset=UTF-8")
    );

    let xml = &response.body;
    assert!(xml.starts_with("<?xml version=\"1.0\" encoding=\"UTF-8\"?>"));
    response
        .assert_see("<title>Hello &amp; welcome</title>")
        .assert_see(&format!("<link>{}</link>", url("/hello-welcome")))
        .assert_see("<dc:creator>Ish Sookun</dc:creator>")
        .assert_see("<category>Linux</category>")
        .assert_see(&format!(
            "<media:content url=\"{}\" medium=\"image\" />",
            url("/content/images/cover.jpg")
        ))
        .assert_see(&format!("src=\"{}\"", url("/content/images/a.jpg")))
        .assert_dont_see("[gallery:photos]");
}

#[tokio::test]
async fn excludes_drafts_scheduled_posts_and_pages_from_the_feed() {
    let mut app = TestApp::new().await;
    create_post(&app.db, post_input("Draft post")).await;
    let mut scheduled = published(post_input("Scheduled post"));
    scheduled.published_at = Some(now() + chrono::Duration::days(1));
    create_post(&app.db, scheduled).await;
    create_post(&app.db, page(published(post_input("About page")))).await;

    app.get("/rss")
        .await
        .assert_ok()
        .assert_dont_see("Draft post")
        .assert_dont_see("Scheduled post")
        .assert_dont_see("About page");
}

#[tokio::test]
async fn serves_a_per_tag_rss_feed_of_the_20_latest_tagged_posts() {
    let mut app = TestApp::new().await;
    let tag = create_tag(&app.db, "openSUSE", "opensuse").await;
    let mut ids = Vec::new();
    for i in 0..21 {
        let mut input = published(post_input(&format!("Tagged post {i}")));
        input.published_at = Some(days_ago(1) + chrono::Duration::minutes(i));
        let post = create_post(&app.db, input).await;
        Post::attach_tag(&app.db, post.id, tag.id, 0).await.unwrap();
        ids.push(post.id);
    }
    let mut oldest = post_input("Oldest tagged post");
    oldest.status = "published".into();
    oldest.published_at = Some(days_ago(365));
    Post::update(&app.db, ids[0], &oldest).await.unwrap();
    create_post(&app.db, published(post_input("Untagged post"))).await;

    let response = app.get("/tag/opensuse/rss").await;
    response.assert_ok();
    assert_eq!(
        response.header("content-type"),
        Some("application/xml; charset=UTF-8")
    );
    response
        .assert_see("<title>SysAdmin Journal · openSUSE</title>")
        .assert_see(&format!("<link>{}</link>", url("/tag/opensuse")))
        .assert_see(&format!("href=\"{}\"", url("/tag/opensuse/rss")))
        .assert_dont_see("Untagged post")
        .assert_dont_see("Oldest tagged post");
    assert_eq!(response.body.matches("<item>").count(), 20);
}

#[tokio::test]
async fn returns_404_for_a_feed_of_an_unknown_tag() {
    let mut app = TestApp::new().await;
    assert_eq!(app.get("/tag/nope/rss").await.status, 404);
}
