<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    private string $basePath = 'images';

    public function index(Request $request): View
    {
        $currentPath = $this->sanitizePath($request->query('path', ''));
        $fullPath = $this->basePath.($currentPath ? '/'.$currentPath : '');

        $disk = Storage::disk('public');

        if (! $disk->directoryExists($fullPath)) {
            abort(404);
        }

        $directories = collect($disk->directories($fullPath))
            ->map(fn (string $dir) => basename($dir))
            ->sort()
            ->values();

        $allFiles = collect($disk->files($fullPath))
            ->filter(fn (string $file) => preg_match('/\.(jpe?g|png|gif|webp|svg|avif|pdf)$/i', $file))
            ->map(fn (string $file) => [
                'name' => basename($file),
                'path' => $file,
                'url' => '/content/'.$file,
                'size' => $disk->size($file),
                'modified' => $disk->lastModified($file),
                'type' => preg_match('/\.pdf$/i', $file) ? 'pdf' : 'image',
            ])
            ->sortByDesc('modified');

        $images = $allFiles->where('type', 'image')->values();
        $pdfs = $allFiles->where('type', 'pdf')->values();

        $breadcrumbs = $this->buildBreadcrumbs($currentPath);

        return view('dashboard.media.index', [
            'currentPath' => $currentPath,
            'directories' => $directories,
            'images' => $images,
            'pdfs' => $pdfs,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function createDirectory(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'current_path' => ['nullable', 'string'],
        ]);

        $currentPath = $this->sanitizePath($request->input('current_path', ''));
        $newDir = $this->basePath
            .($currentPath ? '/'.$currentPath : '')
            .'/'.$request->input('name');

        $disk = Storage::disk('public');

        if ($disk->directoryExists($newDir)) {
            return back()->withErrors(['Directory already exists.']);
        }

        $disk->makeDirectory($newDir);

        return redirect()
            ->route('dashboard.media.index', ['path' => $currentPath])
            ->with('success', 'Directory created.');
    }

    public function deleteDirectory(Request $request): RedirectResponse
    {
        $request->validate([
            'directory' => ['required', 'string'],
            'current_path' => ['nullable', 'string'],
        ]);

        $currentPath = $this->sanitizePath($request->input('current_path', ''));
        $dirName = basename($request->input('directory'));
        $targetPath = $this->basePath
            .($currentPath ? '/'.$currentPath : '')
            .'/'.$dirName;

        $disk = Storage::disk('public');

        if (! $disk->directoryExists($targetPath)) {
            return back()->withErrors(['Directory not found.']);
        }

        if (count($disk->allFiles($targetPath)) > 0 || count($disk->allDirectories($targetPath)) > 0) {
            return back()->withErrors(['Directory is not empty.']);
        }

        $disk->deleteDirectory($targetPath);

        return redirect()
            ->route('dashboard.media.index', ['path' => $currentPath])
            ->with('success', 'Directory deleted.');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'max:20'],
            'photos.*' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,avif,pdf', 'max:10240'],
            'current_path' => ['nullable', 'string'],
        ]);

        $currentPath = $this->sanitizePath($request->input('current_path', ''));
        $storagePath = $this->basePath.($currentPath ? '/'.$currentPath : '');

        $count = 0;
        foreach ($request->file('photos') as $file) {
            $file->storeAs($storagePath, $file->getClientOriginalName(), 'public');
            $count++;
        }

        return redirect()
            ->route('dashboard.media.index', ['path' => $currentPath])
            ->with('success', $count.' '.str('file')->plural($count).' uploaded.');
    }

    public function deletePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'string'],
            'current_path' => ['nullable', 'string'],
        ]);

        $currentPath = $this->sanitizePath($request->input('current_path', ''));
        $fileName = basename($request->input('file'));
        $filePath = $this->basePath
            .($currentPath ? '/'.$currentPath : '')
            .'/'.$fileName;

        $disk = Storage::disk('public');

        if (! $disk->exists($filePath)) {
            return back()->withErrors(['File not found.']);
        }

        $disk->delete($filePath);

        return redirect()
            ->route('dashboard.media.index', ['path' => $currentPath])
            ->with('success', 'Photo deleted.');
    }

    private function sanitizePath(?string $path): string
    {
        $path = trim($path ?? '', '/');
        $path = str_replace('\\', '/', $path);

        // Reject any path traversal attempts
        $segments = array_filter(explode('/', $path), fn (string $s) => $s !== '' && $s !== '.' && $s !== '..');

        return implode('/', $segments);
    }

    /**
     * @return array<int, array{name: string, path: string}>
     */
    private function buildBreadcrumbs(string $currentPath): array
    {
        $crumbs = [['name' => 'images', 'path' => '']];

        if ($currentPath === '') {
            return $crumbs;
        }

        $segments = explode('/', $currentPath);
        $accumulated = '';

        foreach ($segments as $segment) {
            $accumulated = $accumulated ? $accumulated.'/'.$segment : $segment;
            $crumbs[] = ['name' => $segment, 'path' => $accumulated];
        }

        return $crumbs;
    }
}
