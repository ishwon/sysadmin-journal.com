<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::withCount('images')->latest()->get();

        return view('galleries.index', [
            'galleries' => $galleries,
            'seoTitle' => 'Gallery - SysAdmin Journal',
            'seoDescription' => 'Photo collections',
        ]);
    }

    public function show(string $slug): View
    {
        $gallery = Gallery::where('slug', $slug)->with('images')->firstOrFail();

        return view('galleries.show', [
            'gallery' => $gallery,
            'seoTitle' => $gallery->title.' - SysAdmin Journal',
            'seoDescription' => $gallery->description ?: $gallery->title,
        ]);
    }
}
