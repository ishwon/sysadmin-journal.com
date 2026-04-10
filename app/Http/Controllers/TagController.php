<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->posts()
            ->published()
            ->with(['tags', 'authors'])
            ->latest('published_at')
            ->paginate(9);

        return view('tags.show', [
            'tag' => $tag,
            'posts' => $posts,
            'seoTitle' => ($tag->meta_title ?: $tag->name).' - SysAdmin Journal',
            'seoDescription' => $tag->meta_description ?: "Posts tagged with {$tag->name}",
        ]);
    }
}
