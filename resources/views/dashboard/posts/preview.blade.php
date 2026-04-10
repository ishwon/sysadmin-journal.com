<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $post->title }} - SysAdmin Journal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://use.typekit.net/ikg3vvf.css">
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <style>
        figure { display: inline-block; }
        figure img { vertical-align: top; }
        figure figcaption { text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 9px; }
        code { background-color: #e5e7eb; display: inline-flex; padding: 0.125rem 0.75rem; border-radius: 0.25rem; }
        blockquote { font-size: 21px; line-height: 110%; }
        .lato { font-family: Lato, sans-serif; }
        .kg-width-wide { max-width: 1040px; margin-left: auto; margin-right: auto; }
        .kg-width-full { max-width: none; }
        .kg-image-card { margin: 1.5em 0; }
        .kg-image-card img { margin: 0 auto; }
        .kg-embed-card { display: flex; justify-content: center; margin: 1.5em 0; }
        .kg-embed-card iframe { width: 100%; }
        .kg-gallery-container { display: flex; flex-direction: column; gap: 0.75em; margin: 1.5em 0; }
        .kg-gallery-row { display: flex; gap: 0.75em; }
        .kg-gallery-row img { flex: 1; height: auto; object-fit: cover; }
        .kg-gallery-image img { width: 100%; height: auto; }
        .kg-bookmark-card { border: 1px solid #e5e7eb; border-radius: 0.375rem; overflow: hidden; margin: 1.5em 0; }
        .kg-bookmark-card a { display: flex; text-decoration: none; color: inherit; }
        .kg-bookmark-content { padding: 1rem; flex: 1; }
        .kg-bookmark-title { font-weight: 600; }
        .kg-bookmark-description { margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280; }
        .kg-bookmark-metadata { margin-top: 0.5rem; font-size: 0.75rem; color: #9ca3af; }
        .kg-bookmark-thumbnail { width: 200px; }
        .kg-bookmark-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body class="lato">
    <!-- Preview banner -->
    <div class="bg-amber-500 text-white text-center py-2 px-4 text-sm font-medium">
        Preview Mode &mdash;
        <a href="{{ route('dashboard.posts.edit', $post) }}" class="underline">Back to editor</a>
        &middot;
        <a href="{{ route('dashboard.posts.index') }}" class="underline">All posts</a>
    </div>

    <div class="relative py-16 bg-white overflow-hidden">
        <div class="relative px-4 sm:px-6 lg:px-8">
            <div class="text-lg max-w-4xl mx-auto">
                <h1>
                    @if($post->primaryTag())
                    <span class="block text-base text-emerald-600 font-semibold tracking-wide uppercase">
                        {{ $post->primaryTag()->name }}
                    </span>
                    @endif
                    <span class="mt-2 block text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">{{ $post->title }}</span>
                </h1>
                <p class="mt-6 text-xl text-gray-500 leading-8">{{ $post->excerpt }}</p>

                <div class="py-4 flex items-center">
                    @if($post->primaryAuthor())
                    <div class="flex-shrink-0">
                        @if($post->primaryAuthor()->profile_image)
                        <img class="h-10 w-10 rounded-full" src="{{ $post->primaryAuthor()->profile_image }}" alt="">
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ $post->primaryAuthor()->name }}</p>
                        <div class="flex space-x-1 text-sm text-gray-500">
                            <time datetime="{{ $post->published_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}">{{ $post->published_at?->format('d F Y') ?? 'Not published' }}</time>
                            <span aria-hidden="true">&middot;</span>
                            <span>{{ $post->reading_time }} min read</span>
                        </div>
                    </div>
                    @endif
                </div>

                @if($post->feature_image)
                <figure class="mt-4">
                    <img class="rounded" src="{{ $post->feature_image }}" alt="{{ $post->feature_image_alt }}">
                    @if($post->feature_image_caption)
                    <figcaption class="text-left">{!! $post->feature_image_caption !!}</figcaption>
                    @endif
                </figure>
                @endif
            </div>
            <div class="mt-6 max-w-4xl prose prose-emerald prose-lg text-gray-600 mx-auto">
                {!! $post->html !!}
            </div>
        </div>
    </div>

    @include('components.post-gallery', ['post' => $post])
</body>
</html>
