@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'loading' => false,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-accent-600 text-white hover:bg-accent-700 disabled:bg-accent-300 focus-visible:shadow-[var(--shadow-focus)]',
        'secondary' => 'bg-white text-ink-900 border border-ink-200 hover:bg-surface-50 hover:border-ink-300 disabled:text-ink-400 disabled:bg-white focus-visible:shadow-[var(--shadow-focus)]',
        'ghost' => 'text-ink-700 hover:bg-surface-50 hover:text-ink-900 disabled:text-ink-400 focus-visible:shadow-[var(--shadow-focus)]',
        'danger' => 'bg-danger-500 text-white hover:bg-danger-600 disabled:bg-danger-300 focus-visible:shadow-[0_0_0_3px_rgb(177_74_42_/_0.32)]',
        'warning' => 'bg-warning-500 text-white hover:bg-warning-600 focus-visible:shadow-[var(--shadow-focus)]',
        'ink' => 'bg-ink-800 text-white hover:bg-ink-900 disabled:bg-ink-400 focus-visible:shadow-[var(--shadow-focus)]',
    ];

    $sizes = [
        'sm' => 'h-8 px-3 text-[13px]',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-12 px-6 text-[15px]',
    ];

    $classes = collect([
        'inline-flex items-center justify-center gap-2 font-semibold rounded-sm',
        'transition-colors duration-[var(--duration-quick)] ease-[var(--ease-brand)]',
        'focus-visible:outline-none disabled:cursor-not-allowed',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ])->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($loading) {{ $attributes->class($classes) }}>
        @if ($loading)
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
