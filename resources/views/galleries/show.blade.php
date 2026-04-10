@extends('layouts.app')

@section('content')
<div class="bg-white pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
    <div class="relative max-w-6xl mx-auto">
        <div>
            <h1 class="text-3xl tracking-tight font-extrabold text-gray-900 sm:text-4xl">{{ $gallery->title }}</h1>
            @if($gallery->description)
            <p class="mt-3 text-xl text-gray-500 sm:mt-4">{{ $gallery->description }}</p>
            @endif
        </div>
        <div class="mt-12 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" x-data="{ lightbox: null }">
            @foreach($gallery->images as $index => $image)
            <div class="cursor-pointer" @click="lightbox = {{ $index }}">
                <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? $image->caption }}" class="w-full h-48 object-cover rounded-lg hover:opacity-90 transition duration-300">
                @if($image->caption)
                <p class="mt-1 text-xs text-gray-500">{{ $image->caption }}</p>
                @endif
            </div>
            @endforeach

            {{-- Lightbox --}}
            <div x-show="lightbox !== null" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90" @click.self="lightbox = null" @keydown.escape.window="lightbox = null" @keydown.left.window="lightbox = Math.max(0, lightbox - 1)" @keydown.right.window="lightbox = Math.min({{ count($gallery->images) - 1 }}, lightbox + 1)" style="display: none;">
                <button @click="lightbox = null" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
                <button @click="lightbox = Math.max(0, lightbox - 1)" class="absolute left-4 text-white/70 hover:text-white transition p-2">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button @click="lightbox = Math.min({{ count($gallery->images) - 1 }}, lightbox + 1)" class="absolute right-4 text-white/70 hover:text-white transition p-2">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
                @foreach($gallery->images as $index => $image)
                <div x-show="lightbox === {{ $index }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="flex flex-col items-center">
                    <img src="{{ $image->image_path }}" alt="{{ $image->alt_text }}" class="max-h-[80vh] max-w-[90vw] object-contain">
                    @if($image->caption)
                    <p class="mt-3 text-sm text-gray-300">{{ $image->caption }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
