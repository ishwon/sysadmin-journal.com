<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $post->title }} — SysAdmin Journal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap">
    <script>
        (function () {
            try {
                var s = localStorage.theme;
                if (s === 'dark' || (!s && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-white text-ink-800 dark:bg-ink-950 dark:text-ink-100 antialiased">
    <div class="bg-warning-500 text-white text-center py-2 px-4 text-sm font-medium">
        <span class="inline-flex items-center gap-2">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
            Preview mode —
            <a href="{{ route('dashboard.posts.edit', $post) }}" class="underline">Back to editor</a>
            <span aria-hidden="true">·</span>
            <a href="{{ route('dashboard.posts.index') }}" class="underline">All posts</a>
        </span>
    </div>

    <article class="relative py-12 md:py-16 bg-white dark:bg-ink-950">
        <div class="relative px-4 sm:px-6 lg:px-8">
            <header class="max-w-measure mx-auto">
                @if ($post->primaryTag())
                    <p class="text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400">{{ $post->primaryTag()->name }}</p>
                @endif
                <h1 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50 leading-tight">{{ $post->title }}</h1>
                @if ($post->excerpt)
                    <p class="mt-5 font-serif text-xl text-ink-600 dark:text-ink-300 leading-relaxed [&_a]:text-accent-700 hover:[&_a]:underline dark:[&_a]:text-accent-400">{!! $post->excerpt !!}</p>
                @endif

                <div class="mt-8 flex items-center gap-3">
                    @if ($post->primaryAuthor())
                        <div class="shrink-0">
                            @if ($post->primaryAuthor()->profile_image)
                                <img class="h-10 w-10 rounded-full ring-2 ring-white dark:ring-ink-900" src="{{ $post->primaryAuthor()->profile_image }}" alt="">
                            @else
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-ink-800 text-white text-sm font-semibold">
                                    {{ Str::upper(Str::substr($post->primaryAuthor()->name, 0, 2)) }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-ink-900 dark:text-ink-100">{{ $post->primaryAuthor()->name }}</p>
                            <div class="flex gap-1.5 text-xs text-ink-500 dark:text-ink-400">
                                <time datetime="{{ $post->published_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}">{{ $post->published_at?->format('d F Y') ?? 'Not published' }}</time>
                                <span aria-hidden="true">·</span>
                                <span>{{ $post->reading_time }} min read</span>
                            </div>
                        </div>
                    @endif
                </div>
            </header>

            @if ($post->feature_image)
                <figure class="mt-10 max-w-4xl mx-auto">
                    <img class="w-full rounded-lg" src="{{ $post->feature_image }}" alt="{{ $post->feature_image_alt }}">
                    @if ($post->feature_image_caption)
                        <figcaption class="mt-3 text-center text-xs font-mono text-ink-500 dark:text-ink-400">{!! $post->feature_image_caption !!}</figcaption>
                    @endif
                </figure>
            @endif

            <div class="prose-brand mt-10 max-w-measure mx-auto">
                {!! $post->html !!}
            </div>

            @include('components.post-gallery', ['post' => $post])
        </div>
    </article>
</body>
</html>
