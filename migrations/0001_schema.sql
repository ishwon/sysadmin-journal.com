-- Baseline schema. Every statement is idempotent so the Rust application can be
-- pointed at a database that was created by the previous Laravel migrations.

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR NOT NULL,
    slug VARCHAR NOT NULL,
    email VARCHAR NOT NULL,
    email_verified_at DATETIME,
    password VARCHAR NOT NULL,
    bio TEXT,
    profile_image VARCHAR,
    website VARCHAR,
    location VARCHAR,
    facebook VARCHAR,
    twitter VARCHAR,
    ghost_id VARCHAR,
    remember_token VARCHAR,
    created_at DATETIME,
    updated_at DATETIME
);
CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users (email);
CREATE UNIQUE INDEX IF NOT EXISTS users_slug_unique ON users (slug);
CREATE INDEX IF NOT EXISTS users_ghost_id_index ON users (ghost_id);

CREATE TABLE IF NOT EXISTS tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR NOT NULL,
    slug VARCHAR NOT NULL,
    description TEXT,
    feature_image VARCHAR,
    meta_title VARCHAR,
    meta_description TEXT,
    ghost_id VARCHAR,
    created_at DATETIME,
    updated_at DATETIME
);
CREATE UNIQUE INDEX IF NOT EXISTS tags_slug_unique ON tags (slug);
CREATE INDEX IF NOT EXISTS tags_ghost_id_index ON tags (ghost_id);

CREATE TABLE IF NOT EXISTS galleries (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    title VARCHAR NOT NULL,
    slug VARCHAR NOT NULL,
    description TEXT,
    cover_image VARCHAR,
    created_at DATETIME,
    updated_at DATETIME
);
CREATE UNIQUE INDEX IF NOT EXISTS galleries_slug_unique ON galleries (slug);

CREATE TABLE IF NOT EXISTS gallery_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    gallery_id INTEGER NOT NULL,
    image_path VARCHAR NOT NULL,
    caption VARCHAR,
    alt_text VARCHAR,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (gallery_id) REFERENCES galleries (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    title VARCHAR NOT NULL,
    slug VARCHAR NOT NULL,
    html TEXT,
    plaintext TEXT,
    markdown TEXT,
    custom_excerpt TEXT,
    feature_image VARCHAR,
    feature_image_alt VARCHAR,
    feature_image_caption TEXT,
    type VARCHAR NOT NULL DEFAULT 'post',
    status VARCHAR NOT NULL DEFAULT 'draft',
    reading_time INTEGER NOT NULL DEFAULT 1,
    published_at DATETIME,
    meta_title VARCHAR,
    meta_description TEXT,
    og_image VARCHAR,
    og_title VARCHAR,
    og_description TEXT,
    twitter_image VARCHAR,
    twitter_title VARCHAR,
    twitter_description TEXT,
    canonical_url VARCHAR,
    ghost_id VARCHAR,
    created_at DATETIME,
    updated_at DATETIME,
    gallery_id INTEGER,
    FOREIGN KEY (gallery_id) REFERENCES galleries (id) ON DELETE SET NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS posts_slug_unique ON posts (slug);
CREATE INDEX IF NOT EXISTS posts_type_index ON posts (type);
CREATE INDEX IF NOT EXISTS posts_status_index ON posts (status);
CREATE INDEX IF NOT EXISTS posts_ghost_id_index ON posts (ghost_id);
CREATE INDEX IF NOT EXISTS posts_type_status_published_at_index ON posts (type, status, published_at);

CREATE TABLE IF NOT EXISTS post_tag (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    post_id INTEGER NOT NULL,
    tag_id INTEGER NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS post_tag_post_id_tag_id_unique ON post_tag (post_id, tag_id);

CREATE TABLE IF NOT EXISTS post_user (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    post_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS post_user_post_id_user_id_unique ON post_user (post_id, user_id);
