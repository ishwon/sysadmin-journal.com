@extends('layouts.app')

@section('content')
<div class="max-w-[1120px] mx-auto px-6 pt-16 pb-24">
    <header class="pb-6 border-b border-ink-100">
        <p class="eyebrow">/tag/{{ $tag->slug }}/</p>
        <h1 class="display text-5xl mt-2">{{ $tag->name }}</h1>
        <p class="meta mt-3">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p>
        @if ($tag->description ?? null)
            <p class="mt-3 font-serif text-lg text-ink-500 max-w-prose text-pretty">{{ $tag->description }}</p>
        @endif
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
