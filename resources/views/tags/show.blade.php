@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-ink-950 pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
    <div class="relative max-w-lg mx-auto lg:max-w-6xl">
        <header class="pb-8 border-b border-surface-200 dark:border-ink-800">
            <p class="text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400">Tag archive</p>
            <h1 class="mt-2 text-3xl md:text-5xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50">{{ $tag->name }}</h1>
            <p class="mt-3 text-lg font-serif text-ink-500 dark:text-ink-300">
                {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} in this tag
            </p>
            @if ($tag->description ?? null)
                <p class="mt-3 text-base text-ink-600 dark:text-ink-300 max-w-prose">{{ $tag->description }}</p>
            @endif
        </header>

        <div class="mt-12 grid gap-x-8 gap-y-16 lg:grid-cols-3 lg:gap-y-12">
            @foreach ($posts as $post)
                @include('components.post-card', ['post' => $post])
            @endforeach
        </div>

        @if ($posts->hasPages())
            <nav class="mt-16 pt-8 border-t border-surface-200 dark:border-ink-800 flex items-center justify-between gap-4" aria-label="Pagination">
                <p class="text-sm text-ink-500 dark:text-ink-400 hidden sm:block">
                    Page <span class="font-medium text-ink-800 dark:text-ink-100">{{ $posts->currentPage() }}</span>
                    of <span class="font-medium text-ink-800 dark:text-ink-100">{{ $posts->lastPage() }}</span>
                </p>
                <div class="flex-1 flex justify-between sm:justify-end gap-3">
                    @if ($posts->previousPageUrl())
                        <a href="{{ $posts->previousPageUrl() }}" class="inline-flex h-10 items-center gap-1.5 rounded-md border border-ink-200 bg-white px-4 text-sm font-medium text-ink-700 hover:bg-surface-100 transition-colors dark:bg-ink-900 dark:border-ink-700 dark:text-ink-200 dark:hover:bg-ink-800">Newer</a>
                    @endif
                    @if ($posts->nextPageUrl())
                        <a href="{{ $posts->nextPageUrl() }}" class="inline-flex h-10 items-center gap-1.5 rounded-md border border-ink-200 bg-white px-4 text-sm font-medium text-ink-700 hover:bg-surface-100 transition-colors dark:bg-ink-900 dark:border-ink-700 dark:text-ink-200 dark:hover:bg-ink-800">Older</a>
                    @endif
                </div>
            </nav>
        @endif
    </div>
</div>
@endsection
