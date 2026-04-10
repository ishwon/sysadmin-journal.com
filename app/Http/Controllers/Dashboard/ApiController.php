<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function slugCheck(Request $request): JsonResponse
    {
        $title = $request->input('title', '');
        $excludeId = $request->input('exclude_id');
        $slug = Str::slug($title);

        if (! $slug) {
            return response()->json(['slug' => '', 'available' => true]);
        }

        $query = Post::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $available = ! $query->exists();

        if (! $available) {
            $slug = $slug.'-'.now()->format('Ymd');
            $available = ! Post::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists();
        }

        return response()->json(['slug' => $slug, 'available' => $available]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:10240'],
        ]);

        $path = $request->file('image')->store(
            'images/'.now()->format('Y/m'),
            'public'
        );

        return response()->json([
            'path' => '/content/images/'.Str::after($path, 'images/'),
        ]);
    }
}
