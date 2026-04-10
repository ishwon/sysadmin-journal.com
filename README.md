# SysAdmin Journal

A personal blog built with Laravel, migrated from Ghost. Covers topics on Linux system administration, open source, conferences, and more.

## Stack

- Laravel 13 / PHP 8.5
- Tailwind CSS 4 + Alpine.js
- SQLite
- Markdown (league/commonmark)

## Features

- Blog posts and static pages with Markdown/HTML editor
- Tags and multi-author support
- Photo galleries (standalone pages + embeddable via `[gallery:slug]` shortcode)
- SEO meta tags (OpenGraph, Twitter Cards, JSON-LD)
- Full-text search with live modal
- XML sitemap (index + posts, pages, authors, tags)
- Dashboard for managing posts, pages, tags, users, and galleries
- Ghost JSON import command (`php artisan ghost:import`)

## Development

```bash
composer install
npm install && npm run build
php artisan migrate
php artisan storage:link
composer run dev
```

## License

Proprietary.
