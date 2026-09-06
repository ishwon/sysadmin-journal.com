# SysAdmin Journal — v2 redesign, code changes

Drop-in replacements for the Laravel 13 / Tailwind v4 codebase. Every file here mirrors its path in the repo; copy the tree over the project root, then follow the four manual patches below.

```bash
cp -R code/. /path/to/sysadmin-journal.com/
npm run build
php artisan view:clear
```

## What's in the tree

**Theme**
- `resources/css/app.css` — new `@theme` tokens (midnight-indigo `ink-*`, verdigris `accent-*`, silver `surface-*`, pastel-blue `sky-*`, `sage-*`, terracotta `danger-*`), fonts (Public Sans, Source Serif 4, Newsreader, JetBrains Mono, Noto Serif Devanagari), utility classes (`.eyebrow`, `.meta`, `.display`, `.chip`, `.link-rise`), the `.prose-brand` rewrite, figure/figcaption styling, and the reading-progress bar. Existing `accent-*` / `surface-*` class names keep working — only the values changed — so untouched Blade files still render.

**Public site** (`resources/views/`)
- `layouts/app.blade.php` — 56px deep-blue header, mono `/path/` nav, `⌘K · search`, openSUSE button, **no theme toggle**; light hairline footer.
- `posts/index.blade.php` — featured post (first item, page 1 only) + 3-column hairline grid.
- `components/post-card.blade.php`, `posts/show.blade.php`, `pages/show.blade.php`, `tags/show.blade.php`, `authors/show.blade.php`, `galleries/index.blade.php`, `galleries/show.blade.php`, `components/post-gallery.blade.php`, `components/search-modal.blade.php` (adds ⌘K / Ctrl-K shortcut).

**Backoffice**
- `layouts/dashboard.blade.php` — 240px deep-blue sidebar with counts, mono path in the topbar, `@section('page-title')` / `page-aside` slots, no theme toggle.
- `dashboard/index.blade.php` (overview), `dashboard/posts/index`, `pages/index`, `tags/index`, `users/index`, `galleries/index` (hairline tables, inline edit · view · delete), `dashboard/media/index.blade.php`.
- `dashboard/posts/form.blade.php` + `public/js/post-form.js` — title-as-heading editor, format tabs, **insert image dialog** (URL · caption · alt), tag chips, hairline sidebar sections, preview rendered with the real stylesheet.
- `auth/login.blade.php` — split-panel sign-in.
- `components/ui/*` — button, badge, card, table, input, select, textarea, checkbox, radio, nav-item, stat-card, empty-state, dropdown, dropdown-item, breadcrumbs, modal (new `eyebrow` prop), alert.

**Backend**
- `app/Support/Markdown.php` — CommonMark + `wrapFigures()`: turns `![alt](url "caption")` into `<figure><img><figcaption>`.
- `app/Providers/AppServiceProvider.php` — view composer supplying `$navCounts` to the dashboard sidebar. If your provider already has a `boot()`, merge the composer block in instead of overwriting.

## Manual patches (4)

**1. `app/Http/Controllers/Dashboard/PostController.php`** — use the helper so captions render:

```php
use App\Support\Markdown;

private function processContent(?string $content, string $format): ?string
{
    if (! $content) {
        return null;
    }

    return $format === 'markdown' ? Markdown::convert($content) : $content;
}
```

Optionally pass the recent posts and previous/next links the new views use:

```php
// PostController@show (public) — before returning the view
$previous = Post::posts()->published()->where('published_at', '<', $post->published_at)->latest('published_at')->first();
$next     = Post::posts()->published()->where('published_at', '>', $post->published_at)->oldest('published_at')->first();
return view('posts.show', compact('post', 'previous', 'next'));
```

**2. `app/Http/Controllers/Dashboard/PageController.php`** — same swap:

```php
if ($format === 'markdown') {
    return Markdown::convert($content);
}
```

**3. `app/Http/Controllers/DashboardController.php`** — the overview lists recent posts:

```php
'recentPosts' => Post::posts()->latest('updated_at')->limit(6)->get(),
```

**4. `app/Http/Controllers/Dashboard/PostController.php@index`** — the posts list has all / published / drafts filters:

```php
$posts = Post::posts()
    ->when($request->status, fn ($q, $s) => $q->where('status', $s))
    ->latest('published_at')
    ->paginate(20);
```

## Notes

- **Dark mode** was not part of this pass. The `dark` custom-variant and `.dark` classes still compile, but the toggle and the `localStorage.theme` bootstrap were removed from both layouts per the design. Re-add later if wanted.
- **Old dropdown row menus** were replaced with inline mono actions (`edit · view · delete`). `x-ui.dropdown` still exists (used for the sidebar account menu).
- `x-ui.badge` no longer takes `dot`; passing it is harmless.
- `x-ui.card` renders its `title` as a mono `/slug/` eyebrow — this matches the sidebar sections in the editor. Pass `subtitle` for a human line.
- `brand-system.blade.php` and `welcome.blade.php` were not touched.
- Design references: `Blog Redesign.dc.html`, `Backoffice Redesign.dc.html` (in the project root).
