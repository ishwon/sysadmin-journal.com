@extends('layouts.app')

@section('content')
<div class="max-w-[1120px] mx-auto px-6 pt-16 pb-24">
    <header class="pb-6 border-b border-ink-100 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6">
        <div>
            <p class="eyebrow">/gallery/</p>
            <h1 class="display text-5xl mt-2">Galleries</h1>
        </div>
        <p class="font-display italic text-xl leading-snug text-ink-500 sm:text-right">Photo collections from conferences and home.</p>
    </header>

    <div class="grid gap-x-12 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($galleries as $gallery)
            <a href="/gallery/{{ $gallery->slug }}" class="group flex flex-col gap-3 py-8 border-b border-ink-100 text-ink-900 hover:text-accent-700 transition-colors duration-[var(--duration-quick)]">
                <div class="aspect-[16/10] overflow-hidden border-b border-ink-100 bg-surface-50">
                    @if ($gallery->cover_image)
                        <img src="{{ $gallery->cover_image }}" alt="{{ $gallery->title }}" class="h-full w-full object-cover" loading="lazy">
                    @endif
                </div>
                <h3 class="display text-2xl leading-tight">{{ $gallery->title }}</h3>
                @if ($gallery->description)
                    <p class="font-serif text-base leading-relaxed text-ink-500 line-clamp-2">{{ $gallery->description }}</p>
                @endif
                <p class="meta">{{ $gallery->images_count }} {{ Str::plural('photo', $gallery->images_count) }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
