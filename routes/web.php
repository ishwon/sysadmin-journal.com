<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\Dashboard\ApiController;
use App\Http\Controllers\Dashboard\GalleryController as DashboardGalleryController;
use App\Http\Controllers\Dashboard\MediaController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\PostController as DashboardPostController;
use App\Http\Controllers\Dashboard\SitemapController;
use App\Http\Controllers\Dashboard\TagController as DashboardTagController;
use App\Http\Controllers\Dashboard\UserController as DashboardUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard
Route::prefix('dashboard')->middleware('auth')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::resource('posts', DashboardPostController::class);
    Route::resource('pages', PageController::class);
    Route::resource('galleries', DashboardGalleryController::class);
    Route::resource('tags', DashboardTagController::class)->except(['show']);
    Route::resource('users', DashboardUserController::class)->except(['show']);
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media/directory', [MediaController::class, 'createDirectory'])->name('media.create-directory');
    Route::delete('/media/directory', [MediaController::class, 'deleteDirectory'])->name('media.delete-directory');
    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::delete('/media/photo', [MediaController::class, 'deletePhoto'])->name('media.delete-photo');
    Route::post('/sitemap/generate', [SitemapController::class, 'generate'])->name('sitemap.generate');
    Route::post('/api/slug-check', [ApiController::class, 'slugCheck'])->name('api.slug-check');
    Route::post('/api/upload-image', [ApiController::class, 'uploadImage'])->name('api.upload-image');
});

// Public
Route::get('/', [PostController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/gallery', [GalleryController::class, 'index'])->name('galleries.index');
Route::get('/gallery/{slug}', [GalleryController::class, 'show'])->name('galleries.show');
Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/author/{slug}', [AuthorController::class, 'show'])->name('authors.show');

// Post/Page catch-all (must be last)
Route::get('/{slug}', [PostController::class, 'show'])->name('posts.show');
