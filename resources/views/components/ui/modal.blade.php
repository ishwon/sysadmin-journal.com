@props([
    'name',
    'title' => null,
    'eyebrow' => null,
    'size' => 'md',
])

@php
    $sizes = ['sm' => 'max-w-md', 'md' => 'max-w-lg', 'lg' => 'max-w-2xl', 'xl' => 'max-w-4xl'];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $name }}.window="open = true"
    x-on:close-modal-{{ $name }}.window="open = false"
    @keydown.escape.window="open = false"
    x-cloak>
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-ink-950/60 z-40" @click="open = false"></div>
    <div x-show="open" class="fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-6 overflow-y-auto pointer-events-none">
        <div
            x-show="open"
            x-transition:enter="transition ease-[var(--ease-brand)] duration-[var(--duration-base)]"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-[var(--duration-quick)]"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto mt-24 w-full {{ $sizes[$size] ?? $sizes['md'] }} rounded-lg bg-white shadow-xl border border-ink-100 p-6">
            @if ($title || isset($header))
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
                        @if ($title)<h3 class="display text-[26px] mt-1.5">{{ $title }}</h3>@else{{ $header }}@endif
                    </div>
                    <button type="button" @click="open = false" class="text-ink-400 hover:text-ink-900 transition-colors" aria-label="Close">
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.28 3.22a.75.75 0 00-1.06 1.06L8.94 10l-5.72 5.72a.75.75 0 101.06 1.06L10 11.06l5.72 5.72a.75.75 0 101.06-1.06L11.06 10l5.72-5.72a.75.75 0 00-1.06-1.06L10 8.94 4.28 3.22z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            @endif
            <div class="text-sm text-ink-700">{{ $slot }}</div>
            @isset($footer)
                <div class="mt-5 pt-4 border-t border-ink-100 flex items-center justify-end gap-2">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>
