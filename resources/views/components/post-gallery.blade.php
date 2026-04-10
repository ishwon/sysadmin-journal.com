@if($post->gallery && $post->gallery->images->isNotEmpty())
<div class="mt-10 max-w-4xl mx-auto" x-data="{ lightbox: null }">
    <hr class="border-gray-200 mb-8">
    <h3 class="text-xl font-bold text-gray-900 mb-6">Gallery</h3>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($post->gallery->images as $index => $image)
        <figure class="cursor-pointer" @click="lightbox = {{ $index }}">
            <div class="overflow-hidden">
                <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? $image->caption ?? '' }}" class="w-full h-48 object-cover hover:scale-105 transition duration-300">
            </div>
            @if($image->caption)
            <figcaption class="mt-2 text-xs text-gray-500 text-left">{{ $image->caption }}</figcaption>
            @endif
        </figure>
        @endforeach
    </div>

    {{-- Lightbox --}}
    <div x-show="lightbox !== null" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90" @click.self="lightbox = null" @keydown.escape.window="lightbox = null" @keydown.left.window="lightbox = Math.max(0, lightbox - 1)" @keydown.right.window="lightbox = Math.min({{ count($post->gallery->images) - 1 }}, lightbox + 1)" style="display: none;">
        <button @click="lightbox = null" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
        <button @click="lightbox = Math.max(0, lightbox - 1)" class="absolute left-4 text-white/70 hover:text-white transition p-2">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button @click="lightbox = Math.min({{ count($post->gallery->images) - 1 }}, lightbox + 1)" class="absolute right-4 text-white/70 hover:text-white transition p-2">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>
        @foreach($post->gallery->images as $index => $image)
        <div x-show="lightbox === {{ $index }}" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave.duration.0ms class="absolute flex flex-col items-center">
            <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? '' }}" class="max-h-[80vh] max-w-[90vw] object-contain">
            @if($image->caption)
            <p class="mt-3 text-sm text-gray-300">{{ $image->caption }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
