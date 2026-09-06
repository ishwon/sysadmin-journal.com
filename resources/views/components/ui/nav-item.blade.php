@props([
    'href' => '#',
    'active' => false,
    'icon' => null,
    'count' => null,
])

@php
    $classes = collect([
        'group flex items-center gap-3 px-3 py-2 rounded-sm text-sm font-medium transition-colors duration-[var(--duration-quick)]',
        $active ? 'bg-ink-800 text-white' : 'text-ink-200 hover:bg-ink-800 hover:text-white',
    ])->implode(' ');
@endphp

<a href="{{ $href }}" {{ $attributes->class($classes) }}>
    @if ($icon)
        <span class="shrink-0 w-[18px] h-[18px] {{ $active ? 'text-accent-300' : 'text-ink-400 group-hover:text-ink-200' }}">{!! $icon !!}</span>
    @endif
    <span>{{ $slot }}</span>
    @if (!is_null($count))
        <span class="ml-auto font-mono text-[11px] text-ink-400">{{ $count }}</span>
    @endif
</a>
