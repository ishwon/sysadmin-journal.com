@props([
    'href' => null,
    'tone' => 'default',
    'icon' => null,
])

@php
    $tones = [
        'default' => 'text-ink-700 hover:bg-surface-50 hover:text-ink-900',
        'danger' => 'text-danger-600 hover:bg-danger-50',
    ];
    $classes = "flex items-center gap-2 px-4 py-2 text-sm transition-colors duration-[var(--duration-quick)] " . ($tones[$tone] ?? $tones['default']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<span class="shrink-0 w-4 h-4">{!! $icon !!}</span>@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button'])->class($classes . ' w-full text-left') }}>
        @if ($icon)<span class="shrink-0 w-4 h-4">{!! $icon !!}</span>@endif
        {{ $slot }}
    </button>
@endif
