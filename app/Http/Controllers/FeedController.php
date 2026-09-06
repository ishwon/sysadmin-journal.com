<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function index(): Response
    {
        $posts = Post::posts()
            ->published()
            ->with(['tags', 'authors'])
            ->latest('published_at')
            ->limit(20)
            ->get();

        foreach ($posts as $post) {
            $post->html = $this->prepareContent($post->html);
            $post->feature_image = $this->absoluteUrl($post->feature_image);
        }

        return response()
            ->view('feed.rss', [
                'posts' => $posts,
                'title' => 'SysAdmin Journal',
                'description' => 'Thoughts, ideas and stories',
                'lastBuildDate' => $posts->first()?->published_at ?? now(),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Make post HTML safe for feed readers: drop gallery shortcodes, make
     * root-relative links and images absolute, and guard the CDATA wrapper.
     */
    private function prepareContent(?string $html): string
    {
        $html = (string) preg_replace('/\[gallery:[a-z0-9-]+\]/', '', $html ?? '');
        $html = (string) preg_replace('/\b(src|href)="\/(?!\/)/', '$1="'.url('/').'/', $html);

        return str_replace(']]>', ']]]]><![CDATA[>', $html);
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (! $path || str_starts_with($path, 'http')) {
            return $path;
        }

        return url($path);
    }
}
