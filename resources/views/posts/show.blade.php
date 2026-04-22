@extends('layouts.app')

@push('styles')
<style>.reading-progress-fill { height: 100%; background-color: var(--color-accent-500); width: 0%; transition: width 100ms linear; }</style>
@endpush

@section('content')
<div class="reading-progress" aria-hidden="true" x-data="{
    update() {
        const el = document.getElementById('post-body');
        if (!el) return;
        const total = el.offsetHeight - window.innerHeight + el.offsetTop;
        const scrolled = Math.max(0, window.scrollY - el.offsetTop);
        const pct = Math.min(100, Math.max(0, (scrolled / Math.max(1, total)) * 100));
        this.$refs.fill.style.width = pct + '%';
    }
}" x-init="update()" x-on:scroll.window="update()" x-on:resize.window="update()">
    <div class="reading-progress-fill" x-ref="fill"></div>
</div>

<article class="relative py-12 md:py-16 bg-white dark:bg-ink-950">
    <div class="relative px-4 sm:px-6 lg:px-8">
        <header class="max-w-[68ch] mx-auto">
            @if ($post->primaryTag())
                <a href="/tag/{{ $post->primaryTag()->slug }}" class="inline-flex items-center gap-1.5 text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400 hover:text-accent-600 transition-colors">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>
                    {{ $post->primaryTag()->name }}
                </a>
            @endif
            <h1 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50 leading-tight">{{ $post->title }}</h1>
            @if ($post->excerpt)
                <p class="mt-5 font-serif text-xl text-ink-600 dark:text-ink-300 leading-relaxed">{{ $post->excerpt }}</p>
            @endif

            <div class="mt-8 flex items-center gap-3">
                @if ($post->primaryAuthor())
                    <a href="/author/{{ $post->primaryAuthor()->slug }}" class="shrink-0">
                        <span class="sr-only">{{ $post->primaryAuthor()->name }}</span>
                        @if ($post->primaryAuthor()->profile_image)
                            <img class="h-10 w-10 rounded-full ring-2 ring-white dark:ring-ink-900" src="{{ $post->primaryAuthor()->profile_image }}" alt="">
                        @else
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-ink-800 text-white text-sm font-semibold">
                                {{ Str::upper(Str::substr($post->primaryAuthor()->name, 0, 2)) }}
                            </span>
                        @endif
                    </a>
                    <div>
                        <p class="text-sm font-medium text-ink-900 dark:text-ink-100">
                            <a href="/author/{{ $post->primaryAuthor()->slug }}" class="hover:text-accent-700 dark:hover:text-accent-400 transition-colors">{{ $post->primaryAuthor()->name }}</a>
                        </p>
                        <div class="flex gap-1.5 text-xs text-ink-500 dark:text-ink-400">
                            <time datetime="{{ $post->published_at?->format('Y-m-d') }}">{{ $post->published_at?->format('d F Y') }}</time>
                            <span aria-hidden="true">·</span>
                            <span>{{ $post->reading_time }} min read</span>
                        </div>
                    </div>
                @endif
            </div>
        </header>

        @if ($post->feature_image)
            <figure class="mt-10 max-w-4xl mx-auto">
                <img class="w-full rounded-lg" src="{{ $post->feature_image }}" alt="{{ $post->feature_image_alt }}" loading="eager">
                @if ($post->feature_image_caption)
                    <figcaption class="mt-3 text-center text-xs font-mono text-ink-500 dark:text-ink-400">{!! $post->feature_image_caption !!}</figcaption>
                @endif
            </figure>
        @endif

        <div id="post-body" class="prose-brand mt-10 max-w-[68ch] mx-auto">
            {!! $post->html !!}
        </div>

        @include('components.post-gallery', ['post' => $post])
    </div>
</article>
@endsection
