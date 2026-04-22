@extends('layouts.app')

@section('content')
<article class="relative py-12 md:py-16 bg-white dark:bg-ink-950">
    <div class="relative px-4 sm:px-6 lg:px-8">
        <header class="max-w-[68ch] mx-auto">
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-ink-900 dark:text-ink-50 leading-tight">{{ $post->title }}</h1>
        </header>

        @if ($post->feature_image)
            <figure class="mt-8 max-w-4xl mx-auto">
                <img class="w-full rounded-lg" src="{{ $post->feature_image }}" alt="{{ $post->feature_image_alt }}" loading="eager">
                @if ($post->feature_image_caption)
                    <figcaption class="mt-3 text-center text-xs font-mono text-ink-500 dark:text-ink-400">{!! $post->feature_image_caption !!}</figcaption>
                @endif
            </figure>
        @endif

        <div class="prose-brand mt-8 max-w-[68ch] mx-auto">
            {!! $post->html !!}
        </div>
    </div>
</article>
@endsection
