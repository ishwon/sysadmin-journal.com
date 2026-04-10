<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Post;
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
        ]);
    }
}
