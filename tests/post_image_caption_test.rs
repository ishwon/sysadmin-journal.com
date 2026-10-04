mod common;

use common::*;
use sysadmin_journal::commands;
use sysadmin_journal::models::post::Post;

#[tokio::test]
async fn renders_titled_markdown_images_as_captioned_figures_when_saving_a_post() {
    let mut app = TestApp::new().await;
    app.acting_as_new_user().await;

    app.post(
        "/dashboard/posts",
        &[
            ("title", "Captions"),
            ("slug", "captions"),
            (
                "content",
                "Intro\n\n![Alt text](/content/images/a.jpg \"A caption\")",
            ),
            ("content_format", "markdown"),
            ("status", "draft"),
            ("custom_excerpt", ""),
            ("feature_image", ""),
            ("feature_image_alt", ""),
            ("feature_image_caption", ""),
            ("meta_title", ""),
            ("meta_description", ""),
            ("og_image", ""),
            ("twitter_image", ""),
            ("gallery_id", ""),
        ],
    )
    .await
    .assert_redirect("/dashboard/posts");

    let post = Post::find_by_slug(&app.db, "captions")
        .await
        .unwrap()
        .expect("post saved");
    let html = post.html.unwrap();
    assert!(html.contains("<figure><img src=\"/content/images/a.jpg\" alt=\"Alt text\" /><figcaption>A caption</figcaption></figure>"));
    assert!(!html.contains("title="));
    assert_eq!(
        post.markdown.as_deref(),
        Some("Intro\n\n![Alt text](/content/images/a.jpg \"A caption\")")
    );
}

#[tokio::test]
async fn repairs_titled_images_in_already_saved_post_html() {
    let app = TestApp::new().await;
    let mut titled = post_input("Titled");
    titled.html = Some("<p><img src=\"/a.jpg\" alt=\"A\" title=\"Cap\" /></p>".into());
    let post = create_post(&app.db, titled).await;
    let mut plain = post_input("Plain");
    plain.html = Some("<p>Hello</p>".into());
    let untouched = create_post(&app.db, plain).await;

    let updated = commands::wrap_figures(&app.db, false).await.unwrap();
    assert_eq!(updated, 1);

    assert_eq!(
        Post::find(&app.db, post.id)
            .await
            .unwrap()
            .unwrap()
            .html
            .as_deref(),
        Some("<figure><img src=\"/a.jpg\" alt=\"A\" /><figcaption>Cap</figcaption></figure>")
    );
    assert_eq!(
        Post::find(&app.db, untouched.id)
            .await
            .unwrap()
            .unwrap()
            .html
            .as_deref(),
        Some("<p>Hello</p>")
    );
}

#[tokio::test]
async fn a_published_post_page_renders_the_gallery_shortcode_and_seo_data() {
    let mut app = TestApp::new().await;
    let author = create_user(&app.db, "Ish Sookun", "ish").await;
    let mut input = published(post_input("Shortcodes"));
    input.html = Some("<p>Body</p>[gallery:missing]".into());
    let post = create_post(&app.db, input).await;
    Post::attach_author(&app.db, post.id, author.id, 0)
        .await
        .unwrap();

    app.get("/shortcodes")
        .await
        .assert_ok()
        .assert_see("<title>Shortcodes - SysAdmin Journal</title>")
        .assert_see("\"@type\": \"BlogPosting\"")
        .assert_see("Ish Sookun")
        .assert_dont_see("[gallery:missing]");

    assert_eq!(app.get("/nope").await.status, 404);
}
