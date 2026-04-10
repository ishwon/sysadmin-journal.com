@extends('layouts.app')

@section('content')
<div class="relative py-16 bg-white overflow-hidden">
    <div class="relative px-4 sm:px-6 lg:px-8">
        <div class="text-lg max-w-4xl mx-auto">
            <h1>
                <span class="mt-2 block text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">{{ $post->title }}</span>
            </h1>
            @if($post->feature_image)
            <div class="mt-8">
                <img class="rounded" src="{{ $post->feature_image }}" alt="{{ $post->feature_image_alt }}">
                @if($post->feature_image_caption)
                <figcaption class="text-left">{!! $post->feature_image_caption !!}</figcaption>
                @endif
            </div>
            @endif
        </div>
        <div class="mt-6 max-w-4xl prose prose-emerald prose-lg text-gray-600 mx-auto">
            {!! $post->html !!}
        </div>
    </div>
</div>
@endsection
