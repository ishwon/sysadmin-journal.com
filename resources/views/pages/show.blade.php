@extends('layouts.app')

@section('content')
<article class="px-6 pt-16 pb-24">
    <header class="max-w-[68ch] mx-auto">
        <p class="eyebrow">/{{ $post->slug }}/</p>
        <h1 class="display text-4xl md:text-[52px] md:leading-[1.08] mt-4">{{ $post->title }}</h1>
    </header>

    @if ($post->feature_image)
        <figure class="mt-10 max-w-4xl mx-auto">
            <img class="w-full border-b border-ink-100" src="{{ $post->feature_image }}" alt="{{ $post->feature_image_alt }}" loading="eager">
            @if ($post->feature_image_caption)
                <figcaption class="mt-2.5 meta leading-relaxed">{!! $post->feature_image_caption !!}</figcaption>
            @endif
        </figure>
    @endif

    <div class="prose-brand mt-10 max-w-[68ch] mx-auto">
        {!! $post->html !!}
    </div>
</article>
@endsection
