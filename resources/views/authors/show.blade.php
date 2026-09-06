@extends('layouts.app')

@section('content')
<div class="max-w-[1120px] mx-auto px-6 pt-16 pb-24">
    <header class="pb-6 border-b border-ink-100 flex flex-col sm:flex-row gap-6 items-start">
        @if ($author->profile_image)
            <img class="h-20 w-20 rounded-full shrink-0" src="{{ $author->profile_image }}" alt="{{ $author->name }}">
        @else
            <span class="inline-flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-ink-800 text-white font-display text-[28px]">{{ Str::upper(Str::substr($author->name, 0, 2)) }}</span>
        @endif
        <div>
            <p class="eyebrow">/author/{{ $author->slug }}/</p>
            <h1 class="display text-4xl mt-2">{{ $author->name }}</h1>
            @if ($author->bio)
                <p class="mt-3 font-serif text-lg leading-relaxed text-ink-500 max-w-[60ch] text-pretty">{{ $author->bio }}</p>
            @endif
            <p class="meta mt-3">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p>
        </div>
    </header>

    <div class="grid gap-x-12 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($posts as $post)
            @include('components.post-card', ['post' => $post])
        @endforeach
    </div>

    @if ($posts->hasPages())
        <nav class="pt-6 flex items-center justify-between meta" aria-label="Pagination">
            <span>page {{ $posts->currentPage() }} / {{ $posts->lastPage() }}</span>
            <div class="flex gap-6">
                @if ($posts->previousPageUrl())<a href="{{ $posts->previousPageUrl() }}" class="link-rise">← newer posts</a>@endif
                @if ($posts->nextPageUrl())<a href="{{ $posts->nextPageUrl() }}" class="link-rise">older posts →</a>@endif
            </div>
        </nav>
    @endif
</div>
@endsection
