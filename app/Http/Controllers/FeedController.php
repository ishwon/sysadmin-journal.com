<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    private const int ITEM_LIMIT = 20;

    public function index(): Response
    {
        return $this->feedResponse(
            Post::posts(),
            title: 'SysAdmin Journal',
            description: 'Thoughts, ideas and stories',
            link: url('/'),
            self: route('feed'),
        );
    }

    public function tag(string $slug): Response
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        return $this->feedResponse(
            $tag->posts()->posts(),
            title: "SysAdmin Journal · {$tag->name}",
            description: $tag->meta_description ?: "Posts tagged with {$tag->name}",
            link: route('tags.show', $tag->slug),
            self: route('tags.feed', $tag->slug),
        );
    }

    /**
     * @param  Builder<Post>|BelongsToMany<Post, Tag>  $query
     */
    private function feedResponse(Builder|BelongsToMany $query, string $title, string $description, string $link, string $self): Response
    {
        $posts = $query
            ->published()
            ->with(['tags', 'authors'])
            ->latest('published_at')
            ->limit(self::ITEM_LIMIT)
            ->get();

        foreach ($posts as $post) {
            $post->html = $this->prepareContent($post->html);
            $post->feature_image = $this->absoluteUrl($post->feature_image);
        }

        return response()
            ->view('feed.rss', [
                'posts' => $posts,
                'title' => $title,
                'description' => $description,
                'link' => $link,
                'self' => $self,
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
