<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index', [
            'postCount' => Post::posts()->count(),
            'publishedCount' => Post::posts()->published()->count(),
            'pageCount' => Post::pages()->count(),
            'galleryCount' => Gallery::count(),
            'tagCount' => Tag::count(),
            'userCount' => User::count(),
            'sitemapExists' => file_exists(public_path('sitemap.xml')),
        ]);
    }
}
