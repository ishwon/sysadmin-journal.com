@props([
    'shape' => 'line',
    'width' => null,
    'height' => null,
])

@php
    $shapes = [
        'line' => 'h-3 w-full rounded',
        'heading' => 'h-5 w-2/3 rounded',
        'paragraph' => 'h-3 w-full rounded',
        'avatar' => 'h-10 w-10 rounded-full',
        'thumbnail' => 'h-24 w-full rounded-md',
        'button' => 'h-10 w-24 rounded-md',
    ];

    $classes = collect([
        'animate-pulse bg-surface-200 dark:bg-ink-800',
        $shapes[$shape] ?? $shapes['line'],
    ])->implode(' ');

    $style = collect([
        $width ? "width: {$width};" : null,
        $height ? "height: {$height};" : null,
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->class($classes) }} @if ($style) style="{{ $style }}" @endif></div>
