@props([
    'tone' => 'neutral',
    'size' => 'md',
    'dot' => false,
])

@php
    $tones = [
        'neutral' => 'bg-surface-200 text-ink-700 ring-ink-600/10 dark:bg-ink-800 dark:text-ink-200 dark:ring-ink-400/20',
        'success' => 'bg-accent-50 text-accent-700 ring-accent-600/20 dark:bg-accent-900/30 dark:text-accent-300 dark:ring-accent-400/30',
        'warning' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-900/30 dark:text-warning-300 dark:ring-warning-400/30',
        'danger' => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-900/30 dark:text-danger-300 dark:ring-danger-400/30',
        'info' => 'bg-ink-50 text-ink-700 ring-ink-600/20 dark:bg-ink-800 dark:text-ink-200 dark:ring-ink-400/30',
        'ink' => 'bg-ink-800 text-white ring-ink-900/20 dark:bg-ink-100 dark:text-ink-900 dark:ring-white/20',
    ];

    $dotTones = [
        'neutral' => 'bg-ink-500',
        'success' => 'bg-accent-500',
        'warning' => 'bg-warning-500',
        'danger' => 'bg-danger-500',
        'info' => 'bg-ink-400',
        'ink' => 'bg-white',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[11px]',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
    ];

    $classes = collect([
        'inline-flex items-center gap-1.5 rounded-full font-medium ring-1 ring-inset',
        $tones[$tone] ?? $tones['neutral'],
        $sizes[$size] ?? $sizes['md'],
    ])->implode(' ');
@endphp

<span {{ $attributes->class($classes) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotTones[$tone] ?? $dotTones['neutral'] }}"></span>
    @endif
    {{ $slot }}
</span>
