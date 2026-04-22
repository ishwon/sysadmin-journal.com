@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-ink-950 pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
    <div class="relative max-w-lg mx-auto lg:max-w-6xl">
        <header class="pb-8 border-b border-surface-200 dark:border-ink-800">
            <p class="text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400">Photo</p>
            <h1 class="mt-2 text-3xl md:text-5xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50">Galleries</h1>
            <p class="mt-3 text-lg font-serif text-ink-500 dark:text-ink-300">Photo collections.</p>
        </header>

        <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($galleries as $gallery)
                <a href="/gallery/{{ $gallery->slug }}" class="group block">
                    <div class="aspect-[16/10] overflow-hidden rounded-lg bg-surface-100 dark:bg-ink-800">
                        @if ($gallery->cover_image)
                            <img src="{{ $gallery->cover_image }}" alt="{{ $gallery->title }}"
                                class="h-full w-full object-cover group-hover:scale-[1.03] transition-transform duration-[var(--duration-slow)]" loading="lazy">
                        @else
                            <div class="flex items-center justify-center h-full text-ink-300 dark:text-ink-600">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <h3 class="mt-4 text-xl font-semibold text-ink-900 dark:text-ink-50 group-hover:text-accent-700 dark:group-hover:text-accent-400 transition-colors">{{ $gallery->title }}</h3>
                    @if ($gallery->description)
                        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300 line-clamp-2">{{ $gallery->description }}</p>
                    @endif
                    <p class="mt-1 text-xs text-ink-400 dark:text-ink-500">{{ $gallery->images_count }} {{ Str::plural('photo', $gallery->images_count) }}</p>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
