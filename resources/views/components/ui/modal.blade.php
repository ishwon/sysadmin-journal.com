@props([
    'name',
    'title' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal-{{ $name }}.window="open = true"
    x-on:close-modal-{{ $name }}.window="open = false"
    @keydown.escape.window="open = false"
    x-cloak>
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-ink-900/60 backdrop-blur-sm z-40" @click="open = false"></div>
    <div x-show="open" class="fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-6 overflow-y-auto pointer-events-none">
        <div
            x-show="open"
            x-transition:enter="transition ease-[var(--ease-brand)] duration-[var(--duration-base)]"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-[var(--duration-quick)]"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto mt-20 w-full {{ $sizes[$size] ?? $sizes['md'] }} rounded-lg bg-white shadow-xl dark:bg-ink-900 dark:border dark:border-ink-700">
            @if ($title || isset($header))
                <div class="px-6 py-4 border-b border-surface-200 dark:border-ink-700 flex items-center justify-between gap-4">
                    @if ($title)
                        <h3 class="text-lg font-semibold text-ink-900 dark:text-ink-50">{{ $title }}</h3>
                    @else
                        {{ $header }}
                    @endif
                    <button type="button" @click="open = false" class="text-ink-400 hover:text-ink-700 dark:hover:text-ink-200" aria-label="Close">
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.28 3.22a.75.75 0 00-1.06 1.06L8.94 10l-5.72 5.72a.75.75 0 101.06 1.06L10 11.06l5.72 5.72a.75.75 0 101.06-1.06L11.06 10l5.72-5.72a.75.75 0 00-1.06-1.06L10 8.94 4.28 3.22z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            @endif
            <div class="p-6 text-sm text-ink-700 dark:text-ink-200">{{ $slot }}</div>
            @isset($footer)
                <div class="px-6 py-4 border-t border-surface-200 bg-surface-50 rounded-b-lg dark:border-ink-700 dark:bg-ink-950 flex items-center justify-end gap-2">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
