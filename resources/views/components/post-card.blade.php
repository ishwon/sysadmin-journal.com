<article class="py-8 border-b border-ink-100 flex flex-col gap-3">
    <div class="flex items-center gap-2.5">
        @if ($post->primaryTag())
            <a href="/tag/{{ $post->primaryTag()->slug }}" class="chip">{{ $post->primaryTag()->name }}</a>
        @endif
        <time class="meta" datetime="{{ $post->published_at?->format('Y-m-d') }}">{{ $post->published_at?->format('j F Y') }}</time>
    </div>
    <a href="/{{ $post->slug }}" class="text-ink-900 hover:text-accent-700 transition-colors duration-[var(--duration-quick)]">
        <h3 class="display text-2xl leading-tight">{{ $post->title }}</h3>
    </a>
    <p class="font-serif text-base leading-relaxed text-ink-500 text-pretty [&_a]:text-accent-700 hover:[&_a]:underline">{!! $post->excerpt !!}</p>
    <p class="meta mt-auto pt-2">{{ $post->reading_time }} min read</p>
</article>
