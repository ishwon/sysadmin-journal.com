@props([
    'tone' => 'neutral',
    'size' => 'md',
    'dot' => false,
])

@php
    /* Mono, lowercase, 2px radius — status pills live in the terminal register. */
    $tones = [
        'neutral' => 'bg-surface-100 text-ink-500',
        'success' => 'bg-sage-100 text-sage-700',
        'warning' => 'bg-warning-100 text-warning-700',
        'danger' => 'bg-danger-100 text-danger-700',
        'info' => 'bg-sky-100 text-ink-700 ring-1 ring-inset ring-sky-200',
        'ink' => 'bg-ink-800 text-white',
    ];

    $sizes = [
        'sm' => 'px-1.5 py-px text-[10px]',
        'md' => 'px-2 py-px text-[11px]',
        'lg' => 'px-2.5 py-0.5 text-xs',
    ];

    $classes = collect([
        'inline-flex items-center gap-1.5 rounded-xs font-mono lowercase tracking-wide',
        $tones[$tone] ?? $tones['neutral'],
        $sizes[$size] ?? $sizes['md'],
    ])->implode(' ');
@endphp

<span {{ $attributes->class($classes) }}>{{ $slot }}</span>
