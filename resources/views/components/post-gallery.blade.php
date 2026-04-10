@if($post->gallery && $post->gallery->images->isNotEmpty())
<div class="mt-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ lightbox: null }">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ $post->gallery->title }}</h2>
    @if($post->gallery->description)
    <p class="text-gray-500 mb-6">{{ $post->gallery->description }}</p>
    @endif
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($post->gallery->images as $index => $image)
        <div class="cursor-pointer overflow-hidden rounded-lg" @click="lightbox = {{ $index }}">
            <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? $image->caption ?? '' }}" class="w-full h-48 object-cover hover:scale-105 transition duration-300">
        </div>
        @endforeach
    </div>

    {{-- Lightbox --}}
    <div x-show="lightbox !== null" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90" @click.self="lightbox = null" @keydown.escape.window="lightbox = null" @keydown.left.window="lightbox = Math.max(0, lightbox - 1)" @keydown.right.window="lightbox = Math.min({{ count($post->gallery->images) - 1 }}, lightbox + 1)" style="display: none;">
        <button @click="lightbox = null" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
        <button @click="lightbox = Math.max(0, lightbox - 1)" class="absolute left-4 text-white text-3xl">&lsaquo;</button>
        <button @click="lightbox = Math.min({{ count($post->gallery->images) - 1 }}, lightbox + 1)" class="absolute right-4 text-white text-3xl">&rsaquo;</button>
        @foreach($post->gallery->images as $index => $image)
        <div x-show="lightbox === {{ $index }}" class="flex flex-col items-center">
            <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? '' }}" class="max-h-[80vh] max-w-[90vw] object-contain">
            @if($image->caption)
            <p class="mt-3 text-sm text-gray-300">{{ $image->caption }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
