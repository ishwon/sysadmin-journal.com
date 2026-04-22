@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-ink-950 pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
    <div class="relative max-w-lg mx-auto lg:max-w-6xl">
        <header class="pb-8 border-b border-surface-200 dark:border-ink-800">
            <p class="text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400">Author</p>
            <div class="mt-4 flex flex-col lg:flex-row lg:items-center gap-6">
                @if ($author->profile_image)
                    <img class="rounded-full h-24 w-24 ring-4 ring-surface-200 dark:ring-ink-800" src="{{ $author->profile_image }}" alt="{{ $author->name }}">
                @else
                    <span class="inline-flex h-24 w-24 items-center justify-center rounded-full bg-ink-800 text-white text-2xl font-bold">
                        {{ Str::upper(Str::substr($author->name, 0, 2)) }}
                    </span>
                @endif
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50">{{ $author->name }}</h1>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">
                        {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}
                    </p>
                    @if ($author->bio)
                        <p class="mt-3 text-base font-serif text-ink-600 dark:text-ink-300 max-w-prose">{{ $author->bio }}</p>
                    @endif
                </div>
            </div>
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
