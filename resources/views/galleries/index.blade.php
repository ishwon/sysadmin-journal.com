@extends('layouts.app')

@section('content')
<div class="bg-white pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
    <div class="relative max-w-lg mx-auto divide-y-2 divide-gray-200 lg:max-w-6xl">
        <div>
            <h2 class="text-3xl tracking-tight font-extrabold text-gray-900 sm:text-4xl">Gallery</h2>
            <p class="mt-3 text-xl text-gray-500 sm:mt-4">Photo collections</p>
        </div>
        <div class="mt-12 grid gap-8 pt-12 lg:grid-cols-3 lg:gap-x-5 lg:gap-y-12">
            @foreach($galleries as $gallery)
            <a href="/gallery/{{ $gallery->slug }}" class="group">
                <div class="aspect-w-16 aspect-h-9 bg-gray-200 overflow-hidden">
                    @if($gallery->cover_image)
                    <img src="{{ $gallery->cover_image }}" alt="{{ $gallery->title }}" class="object-cover group-hover:opacity-75 transition duration-300">
                    @else
                    <div class="flex items-center justify-center h-48 bg-gray-100">
                        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    @endif
                </div>
                <h3 class="mt-4 text-xl font-semibold text-gray-900 group-hover:text-emerald-600 transition duration-300">{{ $gallery->title }}</h3>
                @if($gallery->description)
                <p class="mt-2 text-base text-gray-500">{{ $gallery->description }}</p>
                @endif
                <p class="mt-1 text-sm text-gray-400">{{ $gallery->images_count }} {{ Str::plural('photo', $gallery->images_count) }}</p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
