<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\Markdown;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Post::pages()->latest('updated_at')->paginate(20);

        return view('dashboard.pages.index', ['pages' => $pages]);
    }

    public function create(): View
    {
        return view('dashboard.pages.create');
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
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);

        $slug = $this->resolveSlug(Str::slug($validated['slug']));
        $html = $this->processContent($validated['content'], $validated['content_format']);

        Post::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'html' => $html,
            'plaintext' => strip_tags($html ?? ''),
            'markdown' => $validated['content_format'] === 'markdown' ? $validated['content'] : null,
            'custom_excerpt' => $validated['custom_excerpt'],
            'feature_image' => $validated['feature_image'],
            'feature_image_alt' => $validated['feature_image_alt'],
            'feature_image_caption' => $validated['feature_image_caption'],
            'type' => 'page',
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
        ]);

        return redirect()->route('dashboard.pages.index')->with('success', 'Page created.');
    }

    public function edit(Post $page): View
    {
        return view('dashboard.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Post $page): RedirectResponse
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
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);

        $slug = $validated['slug'] !== $page->slug ? $this->resolveSlug(Str::slug($validated['slug']), $page->id) : $page->slug;
        $html = $this->processContent($validated['content'], $validated['content_format']);

        $page->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'html' => $html,
            'plaintext' => strip_tags($html ?? ''),
            'markdown' => $validated['content_format'] === 'markdown' ? $validated['content'] : null,
            'custom_excerpt' => $validated['custom_excerpt'],
            'feature_image' => $validated['feature_image'],
            'feature_image_alt' => $validated['feature_image_alt'],
            'feature_image_caption' => $validated['feature_image_caption'],
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' && ! $page->published_at ? now() : $page->published_at,
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
        ]);

        return redirect()->route('dashboard.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Post $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('dashboard.pages.index')->with('success', 'Page deleted.');
    }

    private function resolveSlug(string $slug, ?int $excludeId = null): string
    {
        $query = Post::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            $slug = $slug.'-'.now()->format('Ymd');
        }

        return $slug;
    }

    private function processContent(?string $content, string $format): ?string
    {
        if (! $content) {
            return null;
        }

        if ($format === 'markdown') {
            return Markdown::convert($content);
        }

        return $content;
    }
}
