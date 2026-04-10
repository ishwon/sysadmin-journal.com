@extends('layouts.dashboard')

@section('title', 'Media')

@section('content')
<div x-data="{
    showCreateDir: false,
    showUpload: false,
    lightbox: null,
    lightboxIndex: 0,
    images: {{ Js::from($images) }},
    prevImage() { this.lightboxIndex = (this.lightboxIndex - 1 + this.images.length) % this.images.length; this.lightbox = this.images[this.lightboxIndex].url; },
    nextImage() { this.lightboxIndex = (this.lightboxIndex + 1) % this.images.length; this.lightbox = this.images[this.lightboxIndex].url; }
}">
    {{-- Breadcrumbs --}}
    <nav class="flex items-center text-sm text-gray-500 mb-4">
        @foreach($breadcrumbs as $i => $crumb)
            @if($i > 0)<span class="mx-1">/</span>@endif
            @if($loop->last)
                <span class="font-medium text-gray-900">{{ $crumb['name'] }}</span>
            @else
                <a href="{{ route('dashboard.media.index', ['path' => $crumb['path']]) }}" class="hover:text-emerald-600">{{ $crumb['name'] }}</a>
            @endif
        @endforeach
    </nav>

    {{-- Toolbar --}}
    <div class="flex items-center space-x-3 mb-6">
        <button @click="showCreateDir = true" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            New Folder
        </button>
        <button @click="showUpload = true" class="inline-flex items-center px-3 py-2 bg-emerald-500 text-white rounded-md text-sm font-medium hover:bg-emerald-600 transition">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
            Upload Photos
        </button>
    </div>

    {{-- Create directory modal --}}
    <div x-show="showCreateDir" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="showCreateDir = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm" @click.outside="showCreateDir = false">
            <h3 class="text-lg font-medium text-gray-900 mb-4">New Folder</h3>
            <form method="POST" action="{{ route('dashboard.media.create-directory') }}">
                @csrf
                <input type="hidden" name="current_path" value="{{ $currentPath }}">
                <div class="mb-4">
                    <label for="dir_name" class="block text-sm font-medium text-gray-700 mb-1">Folder name</label>
                    <input type="text" name="name" id="dir_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="e.g. 01" required pattern="[a-zA-Z0-9_-]+">
                    <p class="mt-1 text-xs text-gray-400">Letters, numbers, hyphens and underscores only.</p>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showCreateDir = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-500 rounded-md hover:bg-emerald-600">Create</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Upload modal --}}
    <div x-show="showUpload" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="showUpload = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.outside="showUpload = false">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Photos</h3>
            <form method="POST" action="{{ route('dashboard.media.upload') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="current_path" value="{{ $currentPath }}">
                <div class="mb-4">
                    <label for="photos" class="block text-sm font-medium text-gray-700 mb-1">Select images</label>
                    <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" required>
                    <p class="mt-1 text-xs text-gray-400">Max 10 MB per file. Up to 20 files at once.</p>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showUpload = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-emerald-500 rounded-md hover:bg-emerald-600">Upload</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Directories --}}
    @if($directories->isNotEmpty())
    <div class="mb-6">
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">Folders</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
            @foreach($directories as $dir)
            <div class="group relative bg-white rounded-lg shadow p-3 text-center hover:shadow-md transition">
                <a href="{{ route('dashboard.media.index', ['path' => $currentPath ? $currentPath.'/'.$dir : $dir]) }}" class="block">
                    <svg class="mx-auto h-10 w-10 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                    <span class="mt-1 block text-xs text-gray-700 truncate">{{ $dir }}</span>
                </a>
                <form method="POST" action="{{ route('dashboard.media.delete-directory') }}" class="absolute top-1 right-1 hidden group-hover:block" onsubmit="return confirm('Delete folder \'{{ $dir }}\'? It must be empty.')">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="current_path" value="{{ $currentPath }}">
                    <input type="hidden" name="directory" value="{{ $dir }}">
                    <button type="submit" class="p-1 rounded bg-red-100 text-red-600 hover:bg-red-200" title="Delete folder">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Images --}}
    @if($images->isNotEmpty())
    <div>
        <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">Photos ({{ $images->count() }})</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($images as $index => $image)
            <div class="group relative bg-white rounded-lg shadow overflow-hidden">
                <div class="aspect-square cursor-pointer" @click="lightboxIndex = {{ $index }}; lightbox = images[{{ $index }}].url">
                    <img src="{{ $image['url'] }}" alt="{{ $image['name'] }}" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="p-2">
                    <p class="text-xs text-gray-700 truncate" title="{{ $image['name'] }}">{{ $image['name'] }}</p>
                    <p class="text-xs text-gray-400">{{ number_format($image['size'] / 1024, 0) }} KB</p>
                </div>
                <div class="absolute top-1 right-1 hidden group-hover:flex items-center space-x-1">
                    <button @click.stop="navigator.clipboard.writeText('{{ $image['url'] }}')" class="p-1 rounded bg-white/80 text-gray-600 hover:bg-white" title="Copy path">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </button>
                    <form method="POST" action="{{ route('dashboard.media.delete-photo') }}" class="inline" onsubmit="return confirm('Delete this photo?')">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="current_path" value="{{ $currentPath }}">
                        <input type="hidden" name="file" value="{{ $image['name'] }}">
                        <button type="submit" class="p-1 rounded bg-red-100 text-red-600 hover:bg-red-200" title="Delete photo">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($directories->isEmpty() && $images->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        <p>This folder is empty.</p>
    </div>
    @endif

    {{-- Lightbox --}}
    <div x-show="lightbox" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/90" @keydown.escape.window="lightbox = null" @keydown.left.window="if(lightbox) prevImage()" @keydown.right.window="if(lightbox) nextImage()">
        <button @click="lightbox = null" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <button @click="prevImage()" class="absolute left-4 text-white hover:text-gray-300 z-10" x-show="images.length > 1">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button @click="nextImage()" class="absolute right-4 text-white hover:text-gray-300 z-10" x-show="images.length > 1">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
        <img :src="lightbox" class="max-h-[90vh] max-w-[90vw] object-contain" @click.outside="lightbox = null">
        <div class="absolute bottom-4 text-center text-white text-sm" x-text="images[lightboxIndex]?.name"></div>
    </div>
</div>
@endsection
