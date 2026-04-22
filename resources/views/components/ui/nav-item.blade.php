@props([
    'href' => '#',
    'active' => false,
    'icon' => null,
])

@php
    $classes = collect([
        'group relative flex items-center gap-3 px-6 py-2.5 text-sm font-medium transition-colors',
        $active
            ? 'bg-ink-800 text-accent-400'
            : 'text-ink-300 hover:bg-ink-800 hover:text-white',
    ])->implode(' ');
@endphp

<a href="{{ $href }}" {{ $attributes->class($classes) }}>
    @if ($active)
        <span class="absolute inset-y-0 left-0 w-0.5 bg-accent-500" aria-hidden="true"></span>
    @endif
    @if ($icon)
        <span class="shrink-0 w-5 h-5">{!! $icon !!}</span>
    @endif
    <span>{{ $slot }}</span>
</a>
