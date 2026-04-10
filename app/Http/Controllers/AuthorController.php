<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function show(string $slug): View
    {
        $author = User::where('slug', $slug)->firstOrFail();

        $posts = $author->posts()
            ->posts()
            ->published()
            ->with(['tags', 'authors'])
            ->latest('published_at')
            ->paginate(9);

        return view('authors.show', [
            'author' => $author,
            'posts' => $posts,
            'seoTitle' => $author->name.' - SysAdmin Journal',
            'seoDescription' => $author->bio ?: "Posts by {$author->name}",
        ]);
    }
}
