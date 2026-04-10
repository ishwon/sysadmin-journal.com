<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class SitemapController extends Controller
{
    public function generate(): RedirectResponse
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $now = now()->toIso8601String();

        $this->generateIndex($baseUrl, $now);
        $this->generatePostsSitemap($baseUrl);
        $this->generatePagesSitemap($baseUrl);
        $this->generateAuthorsSitemap($baseUrl);
        $this->generateTagsSitemap($baseUrl);

        return back()->with('success', 'Sitemap generated.');
    }

    private function generateIndex(string $baseUrl, string $now): void
    {
        $sitemaps = ['sitemap-posts.xml', 'sitemap-pages.xml', 'sitemap-authors.xml', 'sitemap-tags.xml'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($sitemaps as $sitemap) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>{$baseUrl}/{$sitemap}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        file_put_contents(public_path('sitemap.xml'), $xml);
    }

    private function generatePostsSitemap(string $baseUrl): void
    {
        $posts = Post::posts()->published()->latest('published_at')->get();

        $xml = $this->openUrlSet();

        foreach ($posts as $post) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}/{$post->slug}</loc>\n";
            $xml .= '    <lastmod>'.$post->updated_at->toIso8601String()."</lastmod>\n";

            if ($post->feature_image) {
                $imageUrl = $this->resolveImageUrl($baseUrl, $post->feature_image);
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>{$imageUrl}</image:loc>\n";
                if ($post->feature_image_caption) {
                    $xml .= '      <image:caption>'.e(strip_tags($post->feature_image_caption))."</image:caption>\n";
                }
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap-posts.xml'), $xml);
    }

    private function generatePagesSitemap(string $baseUrl): void
    {
        $pages = Post::pages()->published()->get();

        $xml = $this->openUrlSet();

        foreach ($pages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}/{$page->slug}</loc>\n";
            $xml .= '    <lastmod>'.$page->updated_at->toIso8601String()."</lastmod>\n";

            if ($page->feature_image) {
                $imageUrl = $this->resolveImageUrl($baseUrl, $page->feature_image);
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>{$imageUrl}</image:loc>\n";
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap-pages.xml'), $xml);
    }

    private function generateAuthorsSitemap(string $baseUrl): void
    {
        $authors = User::has('posts')->get();

        $xml = $this->openUrlSet();

        foreach ($authors as $author) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}/author/{$author->slug}</loc>\n";
            $xml .= '    <lastmod>'.$author->updated_at->toIso8601String()."</lastmod>\n";

            if ($author->profile_image) {
                $imageUrl = $this->resolveImageUrl($baseUrl, $author->profile_image);
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>{$imageUrl}</image:loc>\n";
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap-authors.xml'), $xml);
    }

    private function generateTagsSitemap(string $baseUrl): void
    {
        $tags = Tag::has('posts')->get();

        $xml = $this->openUrlSet();

        foreach ($tags as $tag) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}/tag/{$tag->slug}</loc>\n";
            $xml .= '    <lastmod>'.$tag->updated_at->toIso8601String()."</lastmod>\n";

            if ($tag->feature_image) {
                $imageUrl = $this->resolveImageUrl($baseUrl, $tag->feature_image);
                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>{$imageUrl}</image:loc>\n";
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap-tags.xml'), $xml);
    }

    private function openUrlSet(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
    }

    private function resolveImageUrl(string $baseUrl, string $imagePath): string
    {
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }

        return $baseUrl.$imagePath;
    }
}
