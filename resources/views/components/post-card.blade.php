<article class="group flex flex-col">
    <div>
        @if ($post->primaryTag())
            <a href="/tag/{{ $post->primaryTag()->slug }}" class="inline-block">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-50 dark:bg-accent-900/30 px-3 py-0.5 text-xs font-medium text-accent-700 dark:text-accent-300 ring-1 ring-inset ring-accent-600/20 dark:ring-accent-400/30">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>
                    {{ $post->primaryTag()->name }}
                </span>
            </a>
        @endif
    </div>
    <a href="/{{ $post->slug }}" class="block mt-4">
        <h3 class="text-xl font-semibold text-ink-900 dark:text-ink-50 group-hover:text-accent-700 dark:group-hover:text-accent-400 transition-colors">{{ $post->title }}</h3>
        <p class="mt-3 text-base text-ink-500 dark:text-ink-300">{{ $post->excerpt }}</p>
    </a>
    <div class="mt-6 flex items-center">
        @if ($post->primaryAuthor())
            <div class="shrink-0">
                <a href="/author/{{ $post->primaryAuthor()->slug }}">
                    <span class="sr-only">{{ $post->primaryAuthor()->name }}</span>
                    @if ($post->primaryAuthor()->profile_image)
                        <img class="h-10 w-10 rounded-full ring-2 ring-white dark:ring-ink-900" src="{{ $post->primaryAuthor()->profile_image }}" alt="">
                    @else
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-ink-800 text-white text-sm font-semibold">
                            {{ Str::upper(Str::substr($post->primaryAuthor()->name, 0, 2)) }}
                        </span>
                    @endif
                </a>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-ink-900 dark:text-ink-100">
                    <a href="/author/{{ $post->primaryAuthor()->slug }}" class="hover:text-accent-700 dark:hover:text-accent-400 transition-colors">{{ $post->primaryAuthor()->name }}</a>
                </p>
                <div class="flex gap-1 text-xs text-ink-500 dark:text-ink-400">
                    <time datetime="{{ $post->published_at?->format('Y-m-d') }}">
                        {{ $post->published_at?->format('d F Y') }}
                    </time>
                    <span aria-hidden="true">·</span>
                    <span>{{ $post->reading_time }} min read</span>
                </div>
            </div>
        @endif
    </div>
</article>
