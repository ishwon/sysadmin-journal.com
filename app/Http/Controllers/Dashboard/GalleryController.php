<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::withCount('images')->latest()->paginate(20);

        return view('dashboard.galleries.index', ['galleries' => $galleries]);
    }

    public function create(): View
    {
        return view('dashboard.galleries.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*.path' => ['required', 'string'],
            'images.*.caption' => ['nullable', 'string'],
            'images.*.alt_text' => ['nullable', 'string'],
        ]);

        $gallery = Gallery::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'],
            'cover_image' => $validated['cover_image'],
        ]);

        if (! empty($validated['images'])) {
            foreach ($validated['images'] as $index => $imageData) {
                GalleryImage::create([
                    'gallery_id' => $gallery->id,
                    'image_path' => $imageData['path'],
                    'caption' => $imageData['caption'] ?? null,
                    'alt_text' => $imageData['alt_text'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('dashboard.galleries.index')->with('success', 'Gallery created.');
    }

    public function edit(Gallery $gallery): View
    {
        $gallery->load('images');

        return view('dashboard.galleries.edit', ['gallery' => $gallery]);
    }

    public function update(Request $request, Gallery $gallery): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*.path' => ['required', 'string'],
            'images.*.caption' => ['nullable', 'string'],
            'images.*.alt_text' => ['nullable', 'string'],
        ]);

        $gallery->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'cover_image' => $validated['cover_image'],
        ]);

        $gallery->images()->delete();

        if (! empty($validated['images'])) {
            foreach ($validated['images'] as $index => $imageData) {
                GalleryImage::create([
                    'gallery_id' => $gallery->id,
                    'image_path' => $imageData['path'],
                    'caption' => $imageData['caption'] ?? null,
                    'alt_text' => $imageData['alt_text'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('dashboard.galleries.index')->with('success', 'Gallery updated.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $gallery->delete();

        return redirect()->route('dashboard.galleries.index')->with('success', 'Gallery deleted.');
    }
}
