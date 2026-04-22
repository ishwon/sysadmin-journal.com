@props([
    'tone' => 'light',
])

@php
    $tones = [
        'light' => 'text-ink-500 hover:text-ink-900 hover:bg-surface-100 dark:text-ink-300 dark:hover:text-ink-50 dark:hover:bg-ink-800',
        'dark' => 'text-ink-300 hover:text-white hover:bg-ink-800',
    ];
@endphp

<button type="button" @click="toggleTheme()"
    {{ $attributes->class("inline-flex h-9 w-9 items-center justify-center rounded-md transition-colors duration-[var(--duration-quick)] " . ($tones[$tone] ?? $tones['light'])) }}
    :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'">
    <svg x-show="!dark" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" clip-rule="evenodd" />
    </svg>
    <svg x-show="dark" x-cloak class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464l-.707-.707a1 1 0 00-1.414 1.414l.707.707a1 1 0 001.414-1.414zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm8 8a1 1 0 01-1-1v-1a1 1 0 112 0v1a1 1 0 01-1 1zm-5.657-2.343a1 1 0 00-1.414-1.414l-.707.707a1 1 0 101.414 1.414l.707-.707zm11.314-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707z" clip-rule="evenodd" />
    </svg>
</button>
