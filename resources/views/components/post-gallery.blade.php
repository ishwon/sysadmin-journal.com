@if ($post->gallery && $post->gallery->images->isNotEmpty())
    <div class="mt-12 max-w-4xl mx-auto" x-data="{ lightbox: null }">
        <p class="eyebrow pt-8 border-t border-ink-100">/gallery/{{ $post->gallery->slug }}/</p>
        <h3 class="display text-2xl mt-2 mb-6">{{ $post->gallery->title }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($post->gallery->images as $index => $image)
                <figure class="cursor-pointer m-0" @click="lightbox = {{ $index }}">
                    <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? $image->caption ?? '' }}" class="w-full aspect-[4/3] object-cover border-b border-ink-100" loading="lazy">
                    @if ($image->caption)
                        <figcaption class="mt-2 meta">{{ $image->caption }}</figcaption>
                    @endif
                </figure>
            @endforeach
        </div>

        <div x-show="lightbox !== null" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/95"
            @click.self="lightbox = null"
            @keydown.escape.window="lightbox = null"
            @keydown.left.window="lightbox = Math.max(0, lightbox - 1)"
            @keydown.right.window="lightbox = Math.min({{ count($post->gallery->images) - 1 }}, lightbox + 1)">
            <button @click="lightbox = null" class="absolute top-4 right-4 text-white/80 hover:text-white text-3xl" aria-label="Close">&times;</button>
            <button @click="lightbox = Math.max(0, lightbox - 1)" class="absolute left-4 text-white/70 hover:text-white transition-colors p-2" aria-label="Previous">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button @click="lightbox = Math.min({{ count($post->gallery->images) - 1 }}, lightbox + 1)" class="absolute right-4 text-white/70 hover:text-white transition-colors p-2" aria-label="Next">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" /></svg>
            </button>
            @foreach ($post->gallery->images as $index => $image)
                <div x-show="lightbox === {{ $index }}" x-transition:enter="transition ease-out duration-[var(--duration-base)]"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave.duration.0ms class="absolute flex flex-col items-center">
                    <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? '' }}" class="max-h-[80vh] max-w-[90vw] object-contain">
                    @if ($image->caption)
                        <p class="mt-3 font-mono text-xs tracking-wide text-ink-200">{{ $image->caption }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
