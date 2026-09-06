@extends('layouts.dashboard')

@section('title', 'Media')
@section('page-title', 'Media')

@section('breadcrumbs')
<x-ui.breadcrumbs :items="collect(array_merge([['name' => 'dashboard', 'path' => null]], $breadcrumbs))->map(fn ($c, $i) => [
    'label' => Str::lower($c['name']),
    'href' => $c['path'] === null && $i === 0 ? route('dashboard.index') : route('dashboard.media.index', ['path' => $c['path']]),
])->values()->all()" />
@endsection

@section('page-aside')
<p class="meta">{{ $images->count() + $pdfs->count() }} files</p>
@endsection

@section('actions')
<x-ui.button type="button" variant="secondary" size="sm" x-data @click="$dispatch('open-modal-create-dir')">New folder</x-ui.button>
<x-ui.button type="button" variant="primary" size="sm" x-data @click="$dispatch('open-modal-upload')">Upload</x-ui.button>
@endsection

@section('content')
<div x-data="{
    lightbox: null,
    lightboxIndex: 0,
    images: {{ Js::from($images) }},
    copiedUrl: null,
    copyPath(text) {
        navigator.clipboard.writeText(text);
        this.copiedUrl = text;
        setTimeout(() => this.copiedUrl = null, 1500);
    },
    prevImage() { this.lightboxIndex = (this.lightboxIndex - 1 + this.images.length) % this.images.length; this.lightbox = this.images[this.lightboxIndex].url; },
    nextImage() { this.lightboxIndex = (this.lightboxIndex + 1) % this.images.length; this.lightbox = this.images[this.lightboxIndex].url; }
}">

    {{-- Folders: mono chips --}}
    @if ($directories->isNotEmpty())
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($directories as $dir)
                <div class="group relative">
                    <a href="{{ route('dashboard.media.index', ['path' => $currentPath ? $currentPath . '/' . $dir : $dir]) }}"
                        class="inline-flex items-center gap-2 border border-ink-100 rounded-sm px-3 py-1.5 font-mono text-xs text-ink-700 hover:border-ink-300 hover:text-ink-900 transition-colors">
                        <svg class="h-3.5 w-3.5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" /></svg>{{ $dir }}/
                    </a>
                    <form method="POST" action="{{ route('dashboard.media.delete-directory') }}" class="absolute -top-2 -right-2 hidden group-hover:block" onsubmit="return confirm('Delete folder \'{{ $dir }}\'? It must be empty.')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="current_path" value="{{ $currentPath }}">
                        <input type="hidden" name="directory" value="{{ $dir }}">
                        <button type="submit" class="h-5 w-5 rounded-full bg-white border border-ink-200 text-danger-500 hover:bg-danger-50 text-[11px] leading-none" title="Delete folder">×</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Images --}}
    @if ($images->isNotEmpty())
        <div class="mt-8">
            <h3 class="eyebrow mb-4">/photos/ · {{ $images->count() }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-x-4 gap-y-6">
                @foreach ($images as $index => $image)
                    <figure class="group relative m-0">
                        <img src="{{ $image['url'] }}" alt="{{ $image['name'] }}" class="w-full aspect-square object-cover rounded-sm border border-ink-100 cursor-pointer" loading="lazy"
                            @click="lightboxIndex = {{ $index }}; lightbox = images[{{ $index }}].url">
                        <figcaption class="mt-1.5 meta truncate" title="{{ $image['name'] }}">{{ $image['name'] }}<span class="text-ink-300"> · {{ number_format($image['size'] / 1024, 0) }} KB</span></figcaption>
                        <div class="absolute top-1.5 right-1.5 hidden group-hover:flex items-center gap-1">
                            <button @click.stop="copyPath('{{ $image['url'] }}')" class="h-6 px-1.5 rounded-xs font-mono text-[10px] bg-white/90 border border-ink-100 transition-colors" :class="copiedUrl === '{{ $image['url'] }}' ? 'text-accent-700 border-accent-300' : 'text-ink-700 hover:bg-white'" title="Copy path" x-text="copiedUrl === '{{ $image['url'] }}' ? 'copied' : 'copy'"></button>
                            <form method="POST" action="{{ route('dashboard.media.delete-photo') }}" class="inline" onsubmit="return confirm('Delete this photo?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="current_path" value="{{ $currentPath }}">
                                <input type="hidden" name="file" value="{{ $image['name'] }}">
                                <button type="submit" class="h-6 px-1.5 rounded-xs font-mono text-[10px] bg-white/90 border border-ink-100 text-danger-500 hover:bg-danger-50" title="Delete photo">del</button>
                            </form>
                        </div>
                    </figure>
                @endforeach
            </div>
        </div>
    @endif

    {{-- PDFs --}}
    @if ($pdfs->isNotEmpty())
        <div class="mt-10">
            <h3 class="eyebrow mb-4">/pdfs/ · {{ $pdfs->count() }}</h3>
            <div class="divide-y divide-surface-50 border-t border-ink-100">
                @foreach ($pdfs as $pdf)
                    <div class="group flex items-center justify-between gap-4 py-3">
                        <a href="{{ $pdf['url'] }}" target="_blank" class="font-mono text-xs text-ink-900 hover:text-accent-700 truncate">{{ $pdf['name'] }}</a>
                        <div class="flex items-center gap-4 meta shrink-0">
                            <span>{{ number_format($pdf['size'] / 1024, 0) }} KB</span>
                            <button @click.stop="copyPath('{{ $pdf['url'] }}')" class="link-rise" x-text="copiedUrl === '{{ $pdf['url'] }}' ? 'copied' : 'copy path'"></button>
                            <form method="POST" action="{{ route('dashboard.media.delete-photo') }}" class="inline" onsubmit="return confirm('Delete this file?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="current_path" value="{{ $currentPath }}">
                                <input type="hidden" name="file" value="{{ $pdf['name'] }}">
                                <button type="submit" class="text-danger-500 hover:text-danger-700 font-mono">delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($directories->isEmpty() && $images->isEmpty() && $pdfs->isEmpty())
        <div class="mt-8">
            <x-ui.empty-state title="This folder is empty" description="Upload files or create a sub-folder to get started.">
                <x-slot:action>
                    <x-ui.button type="button" variant="primary" x-data @click="$dispatch('open-modal-upload')">Upload files</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    @endif

    {{-- Lightbox --}}
    <div x-show="lightbox" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/95"
        @keydown.escape.window="lightbox = null"
        @keydown.left.window="if(lightbox) prevImage()"
        @keydown.right.window="if(lightbox) nextImage()">
        <button @click="lightbox = null" class="absolute top-4 right-4 text-white/80 hover:text-white z-10 text-3xl" aria-label="Close">&times;</button>
        <button @click="prevImage()" class="absolute left-4 text-white/70 hover:text-white z-10 p-2" x-show="images.length > 1" aria-label="Previous">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button @click="nextImage()" class="absolute right-4 text-white/70 hover:text-white z-10 p-2" x-show="images.length > 1" aria-label="Next">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        </button>
        <img :src="lightbox" class="max-h-[90vh] max-w-[90vw] object-contain">
        <div class="absolute bottom-4 text-center text-ink-200 font-mono text-xs tracking-wide" x-text="images[lightboxIndex]?.name"></div>
    </div>
</div>

<x-ui.modal name="create-dir" eyebrow="/new-folder/" title="New folder" size="sm">
    <form method="POST" action="{{ route('dashboard.media.create-directory') }}">
        @csrf
        <input type="hidden" name="current_path" value="{{ $currentPath }}">
        <x-ui.input name="name" label="Folder name" placeholder="e.g. 2026" pattern="[a-zA-Z0-9_-]+" required hint="letters, numbers, hyphens and underscores only" class="font-mono text-xs" />
        <div class="mt-5 pt-4 border-t border-ink-100 flex items-center justify-end gap-2">
            <x-ui.button type="button" variant="secondary" size="sm" x-on:click="$dispatch('close-modal-create-dir')">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary" size="sm">Create</x-ui.button>
        </div>
    </form>
</x-ui.modal>

<x-ui.modal name="upload" eyebrow="/upload/" title="Upload files" size="md">
    <form method="POST" action="{{ route('dashboard.media.upload') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="current_path" value="{{ $currentPath }}">
        <label for="photos" class="block text-[13px] font-medium text-ink-900 mb-1.5">Select images or PDFs</label>
        <input type="file" name="photos[]" id="photos" multiple accept="image/*,.pdf,application/pdf" required
            class="w-full text-sm text-ink-500 file:mr-4 file:h-8 file:px-3 file:rounded-sm file:border file:border-ink-200 file:bg-white file:text-[13px] file:font-semibold file:text-ink-900 hover:file:bg-surface-50">
        <p class="mt-2 font-mono text-[11px] text-ink-400">images and pdfs · max 10 MB per file · up to 20 files at once</p>
        <div class="mt-5 pt-4 border-t border-ink-100 flex items-center justify-end gap-2">
            <x-ui.button type="button" variant="secondary" size="sm" x-on:click="$dispatch('close-modal-upload')">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary" size="sm">Upload</x-ui.button>
        </div>
    </form>
</x-ui.modal>
@endsection
