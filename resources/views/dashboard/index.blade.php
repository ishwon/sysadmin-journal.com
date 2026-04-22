@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <x-ui.stat-card label="Posts" :value="$postCount" />
    <x-ui.stat-card label="Published" :value="$publishedCount">
        <x-slot:icon>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
        </x-slot:icon>
    </x-ui.stat-card>
    <x-ui.stat-card label="Pages" :value="$pageCount" />
    <x-ui.stat-card label="Galleries" :value="$galleryCount" />
    <x-ui.stat-card label="Tags" :value="$tagCount" />
    <x-ui.stat-card label="Users" :value="$userCount" />
</div>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-ui.card title="Quick actions">
        <div class="space-y-2">
            <a href="{{ route('dashboard.posts.create') }}" class="flex items-center justify-between rounded-md bg-accent-50 dark:bg-accent-900/20 px-4 py-3 text-sm font-medium text-accent-700 dark:text-accent-300 hover:bg-accent-100 dark:hover:bg-accent-900/30 transition-colors">
                <span class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
                    New post
                </span>
                <svg class="h-4 w-4 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.24a.75.75 0 010 1.06l-4.25 4.24a.75.75 0 01-1.06 0z" clip-rule="evenodd"/></svg>
            </a>
            <a href="{{ route('dashboard.pages.create') }}" class="flex items-center justify-between rounded-md bg-surface-100 dark:bg-ink-800 px-4 py-3 text-sm font-medium text-ink-700 dark:text-ink-200 hover:bg-surface-200 dark:hover:bg-ink-700 transition-colors">
                <span>New page</span>
                <svg class="h-4 w-4 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.24a.75.75 0 010 1.06l-4.25 4.24a.75.75 0 01-1.06 0z" clip-rule="evenodd"/></svg>
            </a>
            <a href="{{ route('dashboard.galleries.create') }}" class="flex items-center justify-between rounded-md bg-surface-100 dark:bg-ink-800 px-4 py-3 text-sm font-medium text-ink-700 dark:text-ink-200 hover:bg-surface-200 dark:hover:bg-ink-700 transition-colors">
                <span>New gallery</span>
                <svg class="h-4 w-4 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.24a.75.75 0 010 1.06l-4.25 4.24a.75.75 0 01-1.06 0z" clip-rule="evenodd"/></svg>
            </a>
        </div>
    </x-ui.card>

    <x-ui.card title="Sitemap" subtitle="Public search engines read this file.">
        <p class="text-sm text-ink-600 dark:text-ink-300 mb-4">
            @if ($sitemapExists)
                Sitemap is live at <a href="/sitemap.xml" target="_blank" class="font-mono text-accent-700 dark:text-accent-400 hover:underline">/sitemap.xml</a>.
            @else
                No sitemap has been generated yet.
            @endif
        </p>
        <form method="POST" action="{{ route('dashboard.sitemap.generate') }}">
            @csrf
            <x-ui.button type="submit" variant="primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                {{ $sitemapExists ? 'Regenerate sitemap' : 'Generate sitemap' }}
            </x-ui.button>
        </form>
    </x-ui.card>
</div>
@endsection
