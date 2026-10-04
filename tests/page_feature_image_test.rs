mod common;

use common::*;
use sysadmin_journal::models::post::Post;

#[tokio::test]
async fn shows_alt_text_and_caption_fields_for_the_feature_image_in_the_page_editor() {
    let mut app = TestApp::new().await;
    let mut input = page(post_input("A page"));
    input.feature_image_alt = Some("Existing alt".into());
    input.feature_image_caption = Some("Existing caption".into());
    let page = create_post(&app.db, input).await;

    app.acting_as_new_user().await;
    app.get(&format!("/dashboard/pages/{}/edit", page.id))
        .await
        .assert_ok()
        .assert_see("name=\"feature_image_alt\"")
        .assert_see("name=\"feature_image_caption\"")
        .assert_see("Existing alt")
        .assert_see("Existing caption");
}

#[tokio::test]
async fn saves_the_feature_image_alt_text_and_caption_when_updating_a_page() {
    let mut app = TestApp::new().await;
    let page = create_post(&app.db, page(post_input("A page"))).await;

    app.acting_as_new_user().await;
    app.put(
        &format!("/dashboard/pages/{}", page.id),
        &[
            ("title", &page.title),
            ("slug", &page.slug),
            ("content", "<p>Hello</p>"),
            ("content_format", "html"),
            ("custom_excerpt", ""),
            ("feature_image", "/content/images/photo.jpg"),
            ("feature_image_alt", "Ish speaking on stage"),
            ("feature_image_caption", "Photo by Arwin Neil Baichoo"),
            ("status", "published"),
            ("meta_title", ""),
            ("meta_description", ""),
        ],
    )
    .await
    .assert_redirect("/dashboard/pages");

    let fresh = Post::find(&app.db, page.id).await.unwrap().unwrap();
    assert_eq!(
        fresh.feature_image.as_deref(),
        Some("/content/images/photo.jpg")
    );
    assert_eq!(
        fresh.feature_image_alt.as_deref(),
        Some("Ish speaking on stage")
    );
    assert_eq!(
        fresh.feature_image_caption.as_deref(),
        Some("Photo by Arwin Neil Baichoo")
    );
    assert!(fresh.published_at.is_some());
}
