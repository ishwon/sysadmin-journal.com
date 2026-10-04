mod common;

use common::*;

#[tokio::test]
async fn the_application_returns_a_successful_response() {
    let mut app = TestApp::new().await;
    app.get("/").await.assert_ok();
}

#[tokio::test]
async fn shows_the_latest_post_as_featured_plus_nine_more_on_the_first_page() {
    let mut app = TestApp::new().await;
    for i in 1..=12 {
        let mut input = published(post_input(&format!("Post number {i}")));
        input.published_at = Some(days_ago(i));
        create_post(&app.db, input).await;
    }

    let first = app.get("/").await;
    first.assert_ok();
    for i in 1..=10 {
        first.assert_see(&format!("Post number {i}"));
    }
    first.assert_dont_see("Post number 11");
    assert_eq!(first.body.matches("Post number 1<").count(), 1);

    app.get("/?page=2")
        .await
        .assert_ok()
        .assert_see("Post number 11")
        .assert_see("Post number 12")
        .assert_dont_see("Post number 1<")
        .assert_dont_see("Post number 10");
}

#[tokio::test]
async fn the_dashboard_requires_authentication() {
    let mut app = TestApp::new().await;
    app.get("/dashboard").await.assert_redirect("/login");
    app.acting_as_new_user().await;
    app.get("/dashboard")
        .await
        .assert_ok()
        .assert_see("/recent-posts/");
}

#[tokio::test]
async fn the_media_library_creates_its_root_directory_on_first_visit() {
    let mut app = TestApp::new().await;
    let images_dir = app.dir.path().join("media/images");
    std::fs::remove_dir_all(&images_dir).unwrap();
    app.acting_as_new_user().await;
    app.get("/dashboard/media")
        .await
        .assert_ok()
        .assert_see("This folder is empty");
    assert!(images_dir.is_dir());
    assert_eq!(app.get("/dashboard/media?path=missing").await.status, 404);
}
