@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-ink-950 pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
    <div class="relative max-w-6xl mx-auto">
        <header class="pb-8 border-b border-surface-200 dark:border-ink-800">
            <a href="/gallery" class="inline-flex items-center gap-1 text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400 hover:text-accent-600 transition-colors">
                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 010 1.06L9.06 10l3.73 3.71a.75.75 0 11-1.06 1.06l-4.25-4.24a.75.75 0 010-1.06l4.25-4.24a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                All galleries
            </a>
            <h1 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50">{{ $gallery->title }}</h1>
            @if ($gallery->description)
                <p class="mt-3 text-lg font-serif text-ink-500 dark:text-ink-300 max-w-prose">{{ $gallery->description }}</p>
            @endif
        </header>

        <div class="mt-12 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" x-data="{ lightbox: null }">
            @foreach ($gallery->images as $index => $image)
                <div class="cursor-pointer group" @click="lightbox = {{ $index }}">
                    <div class="overflow-hidden rounded-md bg-surface-100 dark:bg-ink-800">
                        <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? $image->caption }}"
                            class="w-full h-48 object-cover group-hover:opacity-90 group-hover:scale-[1.02] transition duration-[var(--duration-base)]" loading="lazy">
                    </div>
                    @if ($image->caption)
                        <p class="mt-2 text-xs text-ink-500 dark:text-ink-400 font-mono">{{ $image->caption }}</p>
                    @endif
                </div>
            @endforeach

            {{-- Lightbox --}}
            <div x-show="lightbox !== null" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/95"
                @click.self="lightbox = null"
                @keydown.escape.window="lightbox = null"
                @keydown.left.window="lightbox = Math.max(0, lightbox - 1)"
                @keydown.right.window="lightbox = Math.min({{ count($gallery->images) - 1 }}, lightbox + 1)">
                <button @click="lightbox = null" class="absolute top-4 right-4 text-white/80 hover:text-white text-3xl" aria-label="Close">&times;</button>
                <button @click="lightbox = Math.max(0, lightbox - 1)" class="absolute left-4 text-white/70 hover:text-white transition-colors p-2" aria-label="Previous">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button @click="lightbox = Math.min({{ count($gallery->images) - 1 }}, lightbox + 1)" class="absolute right-4 text-white/70 hover:text-white transition-colors p-2" aria-label="Next">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
                @foreach ($gallery->images as $index => $image)
                    <div x-show="lightbox === {{ $index }}" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave.duration.0ms class="absolute flex flex-col items-center">
                        <img src="{{ $image->image_path }}" alt="{{ $image->alt_text }}" class="max-h-[80vh] max-w-[90vw] object-contain">
                        @if ($image->caption)
                            <p class="mt-3 text-sm text-ink-200">{{ $image->caption }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
