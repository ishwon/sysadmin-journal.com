@extends('layouts.dashboard')

@section('title', 'Dashboard')

@php $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening'); @endphp

@section('page-title', $greeting . ', ' . Str::before(auth()->user()->name ?? 'there', ' ') . '.')
@section('page-aside')<p class="meta">{{ now()->format('l, j F Y') }}</p>@endsection

@section('content')
<div class="flex flex-wrap border-b border-ink-100">
    <x-ui.stat-card label="posts" :value="$postCount" />
    <x-ui.stat-card label="published" :value="$publishedCount" />
    <x-ui.stat-card label="pages" :value="$pageCount" />
    <x-ui.stat-card label="galleries" :value="$galleryCount" />
    <x-ui.stat-card label="tags" :value="$tagCount" />
    <x-ui.stat-card label="users" :value="$userCount" />
</div>

<div class="mt-10 grid grid-cols-1 lg:grid-cols-[3fr_2fr] gap-16">
    <section>
        <div class="flex justify-between items-baseline pb-3 border-b border-ink-100">
            <h2 class="eyebrow">/recent-posts/</h2>
            <a href="{{ route('dashboard.posts.index') }}" class="meta link-rise">all posts →</a>
        </div>
        @forelse ($recentPosts ?? [] as $post)
            <a href="{{ route('dashboard.posts.edit', $post) }}" class="grid grid-cols-[minmax(0,1fr)_auto_auto] gap-4 items-baseline py-3.5 border-b border-surface-50 text-ink-900 hover:text-accent-700 transition-colors">
                <span class="font-display text-lg truncate">{{ $post->title }}</span>
                <x-ui.badge :tone="$post->status === 'published' ? 'success' : 'neutral'">{{ $post->status }}</x-ui.badge>
                <span class="meta">{{ ($post->published_at ?? $post->updated_at)->format('j M Y') }}</span>
            </a>
        @empty
            <p class="py-6 meta">no posts yet — <a href="{{ route('dashboard.posts.create') }}" class="link-rise">write the first one</a></p>
        @endforelse
    </section>

    <section>
        <div class="pb-3 border-b border-ink-100"><h2 class="eyebrow">/housekeeping/</h2></div>
        <div class="flex items-center justify-between gap-4 py-3.5 border-b border-surface-50">
            <div>
                <p class="text-sm font-medium text-ink-900">New post</p>
                <p class="meta mt-0.5">markdown or html</p>
            </div>
            <x-ui.button href="{{ route('dashboard.posts.create') }}" variant="primary" size="sm">Write</x-ui.button>
        </div>
        <div class="flex items-center justify-between gap-4 py-3.5 border-b border-surface-50">
            <div>
                <p class="text-sm font-medium text-ink-900">Sitemap</p>
                <p class="meta mt-0.5">
                    @if ($sitemapExists) live at <a href="/sitemap.xml" target="_blank" class="link-rise">/sitemap.xml</a> @else not generated yet @endif
                </p>
            </div>
            <form method="POST" action="{{ route('dashboard.sitemap.generate') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary" size="sm">{{ $sitemapExists ? 'Regenerate' : 'Generate' }}</x-ui.button>
            </form>
        </div>
        <div class="flex items-center justify-between gap-4 py-3.5 border-b border-surface-50">
            <div>
                <p class="text-sm font-medium text-ink-900">New page · gallery</p>
                <p class="meta mt-0.5">static content and photo sets</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button href="{{ route('dashboard.pages.create') }}" variant="secondary" size="sm">Page</x-ui.button>
                <x-ui.button href="{{ route('dashboard.galleries.create') }}" variant="secondary" size="sm">Gallery</x-ui.button>
            </div>
        </div>
        <div class="flex items-center justify-between gap-4 py-3.5 border-b border-surface-50">
            <div>
                <p class="text-sm font-medium text-ink-900">Ghost import</p>
                <p class="meta mt-0.5">run from the shell</p>
            </div>
            <code class="font-mono text-[11px] bg-surface-50 px-1.5 py-0.5 rounded-xs text-ink-700">php artisan ghost:import</code>
        </div>
    </section>
</div>
@endsection
