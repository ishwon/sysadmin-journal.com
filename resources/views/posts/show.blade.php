@extends('layouts.app')

@section('content')
<div class="relative py-16 bg-white overflow-hidden">
    <div class="relative px-4 sm:px-6 lg:px-8">
        <div class="text-lg max-w-4xl mx-auto">
            <h1>
                @if($post->primaryTag())
                <span class="block text-base text-emerald-600 font-semibold tracking-wide uppercase">
                    <a href="/tag/{{ $post->primaryTag()->slug }}">{{ $post->primaryTag()->name }}</a>
                </span>
                @endif
                <span class="mt-2 block text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">{{ $post->title }}</span>
            </h1>
            <p class="mt-6 text-xl text-gray-500 leading-8">{{ $post->excerpt }}</p>

            <div class="py-4 flex items-center">
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
                        <time datetime="{{ $post->published_at?->format('Y-m-d') }}">{{ $post->published_at?->format('d F Y') }}</time>
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
@endsection
