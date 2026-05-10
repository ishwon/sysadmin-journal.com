<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use League\CommonMark\CommonMarkConverter;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::posts()->with('tags')->latest('updated_at')->paginate(20);

        return view('dashboard.posts.index', ['posts' => $posts]);
    }

    public function create(): View
    {
        $tags = Tag::orderBy('name')->get();
        $galleries = Gallery::orderBy('title')->get();

        return view('dashboard.posts.create', ['tags' => $tags, 'galleries' => $galleries]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'content_format' => ['required', 'in:html,markdown'],
            'custom_excerpt' => ['nullable', 'string'],
            'feature_image' => ['nullable', 'string'],
            'feature_image_alt' => ['nullable', 'string'],
            'feature_image_caption' => ['nullable', 'string'],
            'status' => ['required', 'in:published,draft'],
            'tags' => ['nullable', 'array'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string'],
            'twitter_image' => ['nullable', 'string'],
            'gallery_id' => ['nullable', 'exists:galleries,id'],
        ]);

        $slug = $this->resolveSlug($validated['slug']);
        $html = $this->processContent($validated['content'], $validated['content_format']);
        $plaintext = strip_tags($html ?? '');

        $post = Post::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'html' => $html,
            'plaintext' => $plaintext,
            'markdown' => $validated['content_format'] === 'markdown' ? $validated['content'] : null,
            'custom_excerpt' => $validated['custom_excerpt'],
            'feature_image' => $validated['feature_image'],
            'feature_image_alt' => $validated['feature_image_alt'],
            'feature_image_caption' => $validated['feature_image_caption'],
            'type' => 'post',
            'status' => $validated['status'],
            'reading_time' => Post::calculateReadingTime($plaintext),
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'og_image' => $validated['og_image'],
            'twitter_image' => $validated['twitter_image'],
            'gallery_id' => $validated['gallery_id'],
        ]);

        if (! empty($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        $post->authors()->attach(auth()->id(), ['sort_order' => 0]);

        return redirect()->route('dashboard.posts.index')->with('success', 'Post created.');
    }

    public function show(Post $post): View
    {
        $post->load(['tags', 'authors', 'gallery.images']);
        $post->html = $this->renderGalleryShortcodes($post->html);

        return view('dashboard.posts.preview', ['post' => $post]);
    }

    public function edit(Post $post): View
    {
        $tags = Tag::orderBy('name')->get();
        $galleries = Gallery::orderBy('title')->get();

        return view('dashboard.posts.edit', ['post' => $post, 'tags' => $tags, 'galleries' => $galleries]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'content_format' => ['required', 'in:html,markdown'],
            'custom_excerpt' => ['nullable', 'string'],
            'feature_image' => ['nullable', 'string'],
            'feature_image_alt' => ['nullable', 'string'],
            'feature_image_caption' => ['nullable', 'string'],
            'status' => ['required', 'in:published,draft'],
            'tags' => ['nullable', 'array'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string'],
            'twitter_image' => ['nullable', 'string'],
            'gallery_id' => ['nullable', 'exists:galleries,id'],
        ]);

        $slug = $validated['slug'] !== $post->slug ? $this->resolveSlug($validated['slug'], $post->id) : $post->slug;
        $html = $this->processContent($validated['content'], $validated['content_format']);
        $plaintext = strip_tags($html ?? '');

        $post->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'html' => $html,
            'plaintext' => $plaintext,
            'markdown' => $validated['content_format'] === 'markdown' ? $validated['content'] : null,
            'custom_excerpt' => $validated['custom_excerpt'],
            'feature_image' => $validated['feature_image'],
            'feature_image_alt' => $validated['feature_image_alt'],
            'feature_image_caption' => $validated['feature_image_caption'],
            'status' => $validated['status'],
            'reading_time' => Post::calculateReadingTime($plaintext),
            'published_at' => $validated['status'] === 'published' && ! $post->published_at ? now() : $post->published_at,
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'og_image' => $validated['og_image'],
            'twitter_image' => $validated['twitter_image'],
            'gallery_id' => $validated['gallery_id'],
        ]);

        $post->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('dashboard.posts.edit', $post)->with('success', 'Article updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('dashboard.posts.index')->with('success', 'Post deleted.');
    }

    private function resolveSlug(string $slug, ?int $excludeId = null): string
    {
        $slug = Str::slug($slug);
        $query = Post::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            $slug = $slug.'-'.now()->format('Ymd');

            $counter = 2;
            while (Post::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
                $slug = Str::beforeLast($slug, '-'.($counter - 1)).'-'.$counter;
                $counter++;
            }
        }

        return $slug;
    }

    private function processContent(?string $content, string $format): ?string
    {
        if (! $content) {
            return null;
        }

        if ($format === 'markdown') {
            $converter = new CommonMarkConverter;

            return $converter->convert($content)->getContent();
        }

        return $content;
    }

    private function renderGalleryShortcodes(?string $html): ?string
    {
        if (! $html) {
            return $html;
        }

        return (string) preg_replace_callback('/\[gallery:([a-z0-9-]+)\]/', function ($matches) {
            $gallery = Gallery::where('slug', $matches[1])->with('images')->first();

            if (! $gallery) {
                return '';
            }

            $images = $gallery->images->map(function ($image) {
                $alt = e($image->alt_text ?? $image->caption ?? '');

                return "<div><img src=\"{$image->image_path}\" alt=\"{$alt}\" class=\"w-full h-48 object-cover rounded-lg\"></div>";
            })->join("\n");

            return "<div class=\"grid grid-cols-2 md:grid-cols-3 gap-4 my-8\">{$images}</div>";
        }, $html);
    }
}
