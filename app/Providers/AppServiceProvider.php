<?php

namespace App\Providers;

use App\Models\Gallery;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Sidebar counts for the dashboard layout (posts · pages · galleries · tags · users).
        View::composer('layouts.dashboard', function ($view) {
            $view->with('navCounts', [
                'posts' => Post::posts()->count(),
                'pages' => Post::pages()->count(),
                'galleries' => Gallery::count(),
                'tags' => Tag::count(),
                'users' => User::count(),
            ]);
        });
    }
}
