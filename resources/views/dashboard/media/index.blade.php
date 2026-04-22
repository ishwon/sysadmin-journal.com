@extends('layouts.dashboard')

@section('title', 'Media')

@section('breadcrumbs')
<x-ui.breadcrumbs :items="collect($breadcrumbs)->map(fn ($c, $i) => [
    'label' => $c['name'],
    'href' => $i === count($breadcrumbs) - 1 ? null : route('dashboard.media.index', ['path' => $c['path']]),
])->values()->all()" />
@endsection

@section('actions')
<x-ui.button type="button" variant="secondary" size="sm" x-data @click="$dispatch('open-modal-create-dir')">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
    New folder
</x-ui.button>
<x-ui.button type="button" variant="primary" size="sm" x-data @click="$dispatch('open-modal-upload')">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
    Upload
</x-ui.button>
@endsection

@section('content')
<div x-data="{
    lightbox: null,
    lightboxIndex: 0,
    images: {{ Js::from($images) }},
    copiedUrl: null,
    copyPath(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        this.copiedUrl = text;
        setTimeout(() => this.copiedUrl = null, 1500);
    },
    prevImage() { this.lightboxIndex = (this.lightboxIndex - 1 + this.images.length) % this.images.length; this.lightbox = this.images[this.lightboxIndex].url; },
    nextImage() { this.lightboxIndex = (this.lightboxIndex + 1) % this.images.length; this.lightbox = this.images[this.lightboxIndex].url; }
}">

    {{-- Directories --}}
    @if ($directories->isNotEmpty())
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400 mb-3">Folders</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                @foreach ($directories as $dir)
                    <div class="group relative rounded-lg bg-white dark:bg-ink-900 border border-surface-200 dark:border-ink-700 p-3 text-center hover:shadow-md transition-all duration-[var(--duration-base)]">
                        <a href="{{ route('dashboard.media.index', ['path' => $currentPath ? $currentPath . '/' . $dir : $dir]) }}" class="block">
                            <svg class="mx-auto h-10 w-10 text-warning-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M10 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z" />
                            </svg>
                            <span class="mt-1 block text-xs font-medium text-ink-700 dark:text-ink-200 truncate">{{ $dir }}</span>
                        </a>
                        <form method="POST" action="{{ route('dashboard.media.delete-directory') }}" class="absolute top-1 right-1 hidden group-hover:block" onsubmit="return confirm('Delete folder \'{{ $dir }}\'? It must be empty.')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="current_path" value="{{ $currentPath }}">
                            <input type="hidden" name="directory" value="{{ $dir }}">
                            <button type="submit" class="p-1 rounded bg-danger-50 text-danger-600 hover:bg-danger-100 transition-colors" title="Delete folder">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Images --}}
    @if ($images->isNotEmpty())
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400 mb-3">Photos ({{ $images->count() }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($images as $index => $image)
                    <div class="group relative rounded-lg bg-white dark:bg-ink-900 border border-surface-200 dark:border-ink-700 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="aspect-square cursor-pointer" @click="lightboxIndex = {{ $index }}; lightbox = images[{{ $index }}].url">
                            <img src="{{ $image['url'] }}" alt="{{ $image['name'] }}" class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <div class="p-2">
                            <p class="text-xs font-medium text-ink-800 dark:text-ink-100 truncate" title="{{ $image['name'] }}">{{ $image['name'] }}</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500">{{ number_format($image['size'] / 1024, 0) }} KB</p>
                        </div>
                        <div class="absolute top-1 right-1 hidden group-hover:flex items-center gap-1">
                            <button @click.stop="copyPath('{{ $image['url'] }}')" class="p-1 rounded transition-all duration-[var(--duration-quick)]" :class="copiedUrl === '{{ $image['url'] }}' ? 'bg-accent-500 text-white scale-110' : 'bg-white/80 text-ink-600 hover:bg-white'" title="Copy path">
                                <svg x-show="copiedUrl !== '{{ $image['url'] }}'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                <svg x-show="copiedUrl === '{{ $image['url'] }}'" x-cloak class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </button>
                            <form method="POST" action="{{ route('dashboard.media.delete-photo') }}" class="inline" onsubmit="return confirm('Delete this photo?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="current_path" value="{{ $currentPath }}">
                                <input type="hidden" name="file" value="{{ $image['name'] }}">
                                <button type="submit" class="p-1 rounded bg-danger-50 text-danger-600 hover:bg-danger-100 transition-colors" title="Delete photo">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- PDFs --}}
    @if ($pdfs->isNotEmpty())
        <div class="mb-8">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-400 mb-3">PDFs ({{ $pdfs->count() }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($pdfs as $pdf)
                    <div class="group relative rounded-lg bg-white dark:bg-ink-900 border border-surface-200 dark:border-ink-700 overflow-hidden hover:shadow-md transition-shadow">
                        <a href="{{ $pdf['url'] }}" target="_blank" class="flex items-center justify-center aspect-square bg-surface-50 dark:bg-ink-800">
                            <svg class="h-16 w-16 text-danger-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM6 20V4h5v7h7v9H6z" /><path d="M8 13h2.5c.83 0 1.5.67 1.5 1.5S11.33 16 10.5 16H9v2H8v-5zm1 2h1.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5H9v1zm4-2h2c1.1 0 2 .9 2 2v1c0 1.1-.9 2-2 2h-2v-5zm1 4h1c.55 0 1-.45 1-1v-1c0-.55-.45-1-1-1h-1v3z" /></svg>
                        </a>
                        <div class="p-2">
                            <p class="text-xs font-medium text-ink-800 dark:text-ink-100 truncate" title="{{ $pdf['name'] }}">{{ $pdf['name'] }}</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500">{{ number_format($pdf['size'] / 1024, 0) }} KB</p>
                        </div>
                        <div class="absolute top-1 right-1 hidden group-hover:flex items-center gap-1">
                            <button @click.stop="copyPath('{{ $pdf['url'] }}')" class="p-1 rounded transition-all duration-[var(--duration-quick)]" :class="copiedUrl === '{{ $pdf['url'] }}' ? 'bg-accent-500 text-white scale-110' : 'bg-white/80 text-ink-600 hover:bg-white'" title="Copy path">
                                <svg x-show="copiedUrl !== '{{ $pdf['url'] }}'" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                <svg x-show="copiedUrl === '{{ $pdf['url'] }}'" x-cloak class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </button>
                            <form method="POST" action="{{ route('dashboard.media.delete-photo') }}" class="inline" onsubmit="return confirm('Delete this file?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="current_path" value="{{ $currentPath }}">
                                <input type="hidden" name="file" value="{{ $pdf['name'] }}">
                                <button type="submit" class="p-1 rounded bg-danger-50 text-danger-600 hover:bg-danger-100 transition-colors" title="Delete file">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($directories->isEmpty() && $images->isEmpty() && $pdfs->isEmpty())
        <x-ui.empty-state title="This folder is empty" description="Upload files or create a sub-folder to get started.">
            <x-slot:action>
                <x-ui.button type="button" variant="primary" x-data @click="$dispatch('open-modal-upload')">Upload files</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    @endif

    {{-- Lightbox --}}
    <div x-show="lightbox" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/95"
        @keydown.escape.window="lightbox = null"
        @keydown.left.window="if(lightbox) prevImage()"
        @keydown.right.window="if(lightbox) nextImage()">
        <button @click="lightbox = null" class="absolute top-4 right-4 text-white/80 hover:text-white z-10" aria-label="Close">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <button @click="prevImage()" class="absolute left-4 text-white/70 hover:text-white z-10" x-show="images.length > 1" aria-label="Previous">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button @click="nextImage()" class="absolute right-4 text-white/70 hover:text-white z-10" x-show="images.length > 1" aria-label="Next">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        </button>
        <img :src="lightbox" class="max-h-[90vh] max-w-[90vw] object-contain">
        <div class="absolute bottom-4 text-center text-white text-sm font-mono" x-text="images[lightboxIndex]?.name"></div>
    </div>
</div>

{{-- Modals --}}
<x-ui.modal name="create-dir" title="New folder" size="sm">
    <form method="POST" action="{{ route('dashboard.media.create-directory') }}">
        @csrf
        <input type="hidden" name="current_path" value="{{ $currentPath }}">
        <x-ui.input name="name" label="Folder name" placeholder="e.g. 01" pattern="[a-zA-Z0-9_-]+" required hint="Letters, numbers, hyphens and underscores only." />
        <div class="mt-4 flex items-center justify-end gap-2">
            <x-ui.button type="button" variant="ghost" x-on:click="$dispatch('close-modal-create-dir')">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">Create</x-ui.button>
        </div>
    </form>
</x-ui.modal>

<x-ui.modal name="upload" title="Upload files" size="md">
    <form method="POST" action="{{ route('dashboard.media.upload') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="current_path" value="{{ $currentPath }}">
        <label for="photos" class="block text-sm font-medium text-ink-800 dark:text-ink-100 mb-1.5">Select images or PDFs</label>
        <input type="file" name="photos[]" id="photos" multiple accept="image/*,.pdf,application/pdf"
            class="w-full text-sm text-ink-500 dark:text-ink-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-accent-50 file:text-accent-700 hover:file:bg-accent-100 dark:file:bg-accent-900/30 dark:file:text-accent-300" required>
        <p class="mt-2 text-xs text-ink-500 dark:text-ink-400">Images and PDFs. Max 10 MB per file. Up to 20 files at once.</p>
        <div class="mt-4 flex items-center justify-end gap-2">
            <x-ui.button type="button" variant="ghost" x-on:click="$dispatch('close-modal-upload')">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="primary">Upload</x-ui.button>
        </div>
    </form>
</x-ui.modal>
@endsection
