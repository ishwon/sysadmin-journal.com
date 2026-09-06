@extends('layouts.app')

@section('content')
<div class="max-w-[1120px] mx-auto px-6 pt-16 pb-24">
    <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6 pb-6 border-b border-ink-100">
        <div>
            <p class="eyebrow">/journal/</p>
            <h1 class="display text-5xl mt-2">Latest posts</h1>
        </div>
        <p class="font-display italic text-xl leading-snug text-ink-500 sm:text-right sm:max-w-[36ch] text-pretty">Thoughts, ideas and stories from the command line.</p>
    </header>

    @php $featured = $posts->onFirstPage() ? $featured : null; @endphp

    @if ($featured)
        <article class="grid lg:grid-cols-2 gap-12 py-12 border-b border-ink-100 items-center">
            <div>
                @if ($featured->primaryTag())
                    <p class="eyebrow">/journal/{{ $featured->primaryTag()->slug }}/</p>
                @endif
                <a href="/{{ $featured->slug }}" class="block mt-3 text-ink-900 hover:text-accent-700 transition-colors duration-[var(--duration-quick)]">
                    <h2 class="display text-4xl">{{ $featured->title }}</h2>
                </a>
                <p class="mt-4 font-serif text-lg leading-relaxed text-ink-500 text-pretty [&_a]:text-accent-700 hover:[&_a]:underline">{!! $featured->excerpt !!}</p>
                <p class="meta mt-5">{{ $featured->published_at?->format('j F Y') }} · {{ $featured->reading_time }} min read</p>
            </div>
            @if ($featured->feature_image)
                <a href="/{{ $featured->slug }}" class="block">
                    <img class="w-full aspect-[16/10] object-cover rounded-md border border-ink-100" src="{{ $featured->feature_image }}" alt="{{ $featured->feature_image_alt }}">
                </a>
            @else
                <div class="hidden lg:block aspect-[16/10] rounded-md border border-ink-100 bg-surface-50"></div>
            @endif
        </article>
    @endif

    <div class="grid gap-x-12 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($posts as $post)
            @include('components.post-card', ['post' => $post])
        @endforeach
    </div>

    @if ($posts->hasPages())
        <nav class="pt-6 flex items-center justify-between meta" aria-label="Pagination">
            <span>page {{ $posts->currentPage() }} / {{ $posts->lastPage() }}</span>
            <div class="flex gap-6">
                @if ($posts->previousPageUrl())
                    <a href="{{ $posts->previousPageUrl() }}" class="link-rise">← newer posts</a>
                @endif
                @if ($posts->nextPageUrl())
                    <a href="{{ $posts->nextPageUrl() }}" class="link-rise">older posts →</a>
                @endif
            </div>
        </nav>
    @endif
</div>
@endsection
