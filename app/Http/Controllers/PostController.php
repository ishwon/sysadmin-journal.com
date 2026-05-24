<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::posts()
            ->published()
            ->with(['tags', 'authors'])
            ->latest('published_at')
            ->paginate(9);

        return view('posts.index', [
            'posts' => $posts,
            'seoTitle' => 'SysAdmin Journal',
            'seoDescription' => 'Thoughts, ideas and stories',
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with(['tags', 'authors', 'gallery.images'])
            ->firstOrFail();

        $post->html = $this->renderGalleryShortcodes($post->html);

        $seoTitle = $post->meta_title ?: $post->title.' - SysAdmin Journal';
        $seoDescription = $post->meta_description ?: $post->plain_excerpt;

        $viewData = [
            'post' => $post,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'seoImage' => $post->og_image ?: $post->feature_image,
            'seoType' => $post->type === 'post' ? 'article' : 'website',
            'twitterImage' => $post->twitter_image ?: $post->og_image ?: $post->feature_image,
            'canonicalUrl' => $post->canonical_url ?: url('/'.$post->slug),
        ];

        if ($post->type === 'page') {
            return view('pages.show', $viewData);
        }

        return view('posts.show', $viewData);
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
