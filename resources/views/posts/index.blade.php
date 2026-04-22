@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-ink-950 pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
    <div class="relative max-w-lg mx-auto lg:max-w-6xl">
        <header class="pb-8 border-b border-surface-200 dark:border-ink-800">
            <p class="text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400">Journal</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50 sm:text-4xl">Latest posts</h1>
            <p class="mt-3 text-lg font-serif text-ink-600 dark:text-ink-300">Thoughts, ideas and stories from the command line.</p>
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
                        <a href="{{ $posts->previousPageUrl() }}" class="inline-flex h-10 items-center gap-1.5 rounded-md border border-ink-200 bg-white px-4 text-sm font-medium text-ink-700 hover:bg-surface-100 transition-colors dark:bg-ink-900 dark:border-ink-700 dark:text-ink-200 dark:hover:bg-ink-800">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 010 1.06L9.06 10l3.73 3.71a.75.75 0 11-1.06 1.06l-4.25-4.24a.75.75 0 010-1.06l4.25-4.24a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                            Newer
                        </a>
                    @endif
                    @if ($posts->nextPageUrl())
                        <a href="{{ $posts->nextPageUrl() }}" class="inline-flex h-10 items-center gap-1.5 rounded-md border border-ink-200 bg-white px-4 text-sm font-medium text-ink-700 hover:bg-surface-100 transition-colors dark:bg-ink-900 dark:border-ink-700 dark:text-ink-200 dark:hover:bg-ink-800">
                            Older
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.24a.75.75 0 010 1.06l-4.25 4.24a.75.75 0 01-1.06 0z" clip-rule="evenodd"/></svg>
                        </a>
                    @endif
                </div>
            </nav>
        @endif
    </div>
</div>
@endsection
