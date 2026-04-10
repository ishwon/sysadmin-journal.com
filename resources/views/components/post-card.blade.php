<div>
    <div>
        @if($post->primaryTag())
        <a href="/tag/{{ $post->primaryTag()->slug }}" class="inline-block">
            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-emerald-600">
                {{ $post->primaryTag()->name }}
            </span>
        </a>
        @endif
    </div>
    <a href="/{{ $post->slug }}" class="block mt-4">
        <p class="text-xl font-semibold text-gray-900">{{ $post->title }}</p>
        <p class="mt-3 text-base text-gray-500">{{ $post->excerpt }}</p>
    </a>
    <div class="mt-6 flex items-center">
        @if($post->primaryAuthor())
        <div class="flex-shrink-0">
            <a href="/author/{{ $post->primaryAuthor()->slug }}">
                <span class="sr-only">{{ $post->primaryAuthor()->name }}</span>
                @if($post->primaryAuthor()->profile_image)
                <img class="h-10 w-10 rounded-full" src="{{ $post->primaryAuthor()->profile_image }}" alt="">
                @endif
            </a>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-gray-900">
                <a href="/author/{{ $post->primaryAuthor()->slug }}">{{ $post->primaryAuthor()->name }}</a>
            </p>
            <div class="flex space-x-1 text-sm text-gray-500">
                <time datetime="{{ $post->published_at?->format('Y-m-d') }}">
                    {{ $post->published_at?->format('d F Y') }}
                </time>
                <span aria-hidden="true">&middot;</span>
                <span>{{ $post->reading_time }} min read</span>
            </div>
        </div>
        @endif
    </div>
</div>
