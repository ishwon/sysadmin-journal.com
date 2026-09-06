@extends('layouts.app')

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

<article class="px-6 pt-16 pb-24">
    <header class="max-w-measure mx-auto text-lg">
        <p class="eyebrow">
            <a href="/" class="link-rise">/journal/</a>@if ($post->primaryTag())<a href="/tag/{{ $post->primaryTag()->slug }}" class="link-rise">{{ $post->primaryTag()->slug }}/</a>@endif
        </p>
        <h1 class="display text-4xl md:text-[52px] md:leading-[1.08] mt-4">{{ $post->title }}</h1>
        @if ($post->excerpt)
            <p class="mt-5 font-display italic text-[22px] leading-snug text-ink-500 text-pretty [&_a]:text-accent-700 hover:[&_a]:underline">{!! $post->excerpt !!}</p>
        @endif
        <div class="mt-7 pt-4 border-t border-ink-100 flex items-center gap-3 meta">
            @if ($post->primaryAuthor())
                <a href="/author/{{ $post->primaryAuthor()->slug }}" class="shrink-0">
                    <span class="sr-only">{{ $post->primaryAuthor()->name }}</span>
                    @if ($post->primaryAuthor()->profile_image)
                        <img class="h-7 w-7 rounded-full" src="{{ $post->primaryAuthor()->profile_image }}" alt="">
                    @else
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-ink-800 text-white font-display text-xs">{{ Str::upper(Str::substr($post->primaryAuthor()->name, 0, 2)) }}</span>
                    @endif
                </a>
                <a href="/author/{{ $post->primaryAuthor()->slug }}" class="text-ink-900 hover:text-accent-700 transition-colors">{{ $post->primaryAuthor()->name }}</a>
                <span aria-hidden="true">·</span>
            @endif
            <time datetime="{{ $post->published_at?->format('Y-m-d') }}">{{ $post->published_at?->format('j F Y') }}</time>
            <span aria-hidden="true">·</span>
            <span>{{ $post->reading_time }} min read</span>
        </div>
    </header>

    @if ($post->feature_image)
        <figure class="mt-12 max-w-4xl mx-auto">
            <img class="w-full border-b border-ink-100" src="{{ $post->feature_image }}" alt="{{ $post->feature_image_alt }}" loading="eager">
            @if ($post->feature_image_caption)
                <figcaption class="mt-2.5 meta leading-relaxed">{!! $post->feature_image_caption !!}</figcaption>
            @endif
        </figure>
    @endif

    <div id="post-body" class="prose-brand mt-12 max-w-measure mx-auto">
        {!! $post->html !!}
        <p class="section-end" aria-hidden="true">॥</p>
    </div>

    @include('components.post-gallery', ['post' => $post])

    @isset($previous, $next)
        <footer class="max-w-measure mx-auto mt-12 pt-6 border-t border-ink-100 flex justify-between gap-6 meta">
            @if ($previous)<a href="/{{ $previous->slug }}" class="link-rise">← {{ $previous->title }}</a>@else<span></span>@endif
            @if ($next)<a href="/{{ $next->slug }}" class="link-rise text-right">{{ $next->title }} →</a>@endif
        </footer>
    @endisset
</article>
@endsection
