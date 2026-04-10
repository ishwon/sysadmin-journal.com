<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $posts = Post::posts()
            ->published()
            ->with(['tags'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('plaintext', 'like', "%{$query}%");
            })
            ->latest('published_at')
            ->limit(10)
            ->get()
            ->map(fn ($post) => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => Str::limit($post->excerpt, 100),
                'tag' => $post->primaryTag()?->name,
                'date' => $post->published_at?->format('d F Y'),
            ]);

        return response()->json($posts);
    }
}
