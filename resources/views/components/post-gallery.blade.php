@if ($post->gallery && $post->gallery->images->isNotEmpty())
    <div class="mt-10 max-w-4xl mx-auto" x-data="{ lightbox: null }">
        <hr class="border-surface-200 dark:border-ink-700 mb-8">
        <h3 class="text-xl font-bold text-ink-900 dark:text-ink-50 mb-6">Gallery</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach ($post->gallery->images as $index => $image)
                <figure class="cursor-pointer group" @click="lightbox = {{ $index }}">
                    <div class="overflow-hidden rounded-md bg-surface-100 dark:bg-ink-800">
                        <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? $image->caption ?? '' }}"
                            class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-[var(--duration-slow)]" loading="lazy">
                    </div>
                    @if ($image->caption)
                        <figcaption class="mt-2 text-xs text-ink-500 dark:text-ink-400 text-left font-mono">{{ $image->caption }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>

        {{-- Lightbox --}}
        <div x-show="lightbox !== null" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/95"
            @click.self="lightbox = null"
            @keydown.escape.window="lightbox = null"
            @keydown.left.window="lightbox = Math.max(0, lightbox - 1)"
            @keydown.right.window="lightbox = Math.min({{ count($post->gallery->images) - 1 }}, lightbox + 1)">
            <button @click="lightbox = null" class="absolute top-4 right-4 text-white/80 hover:text-white text-3xl" aria-label="Close">&times;</button>
            <button @click="lightbox = Math.max(0, lightbox - 1)" class="absolute left-4 text-white/70 hover:text-white transition-colors p-2" aria-label="Previous">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button @click="lightbox = Math.min({{ count($post->gallery->images) - 1 }}, lightbox + 1)" class="absolute right-4 text-white/70 hover:text-white transition-colors p-2" aria-label="Next">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
            @foreach ($post->gallery->images as $index => $image)
                <div x-show="lightbox === {{ $index }}" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave.duration.0ms class="absolute flex flex-col items-center">
                    <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? '' }}" class="max-h-[80vh] max-w-[90vw] object-contain">
                    @if ($image->caption)
                        <p class="mt-3 text-sm text-ink-200">{{ $image->caption }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
