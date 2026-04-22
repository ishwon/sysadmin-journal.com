@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'md',
    'hoverable' => false,
])

@php
    $pads = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
    ];

    $classes = collect([
        'rounded-lg bg-white border border-surface-200 shadow-sm',
        'dark:bg-ink-900 dark:border-ink-700',
        $hoverable ? 'transition-shadow duration-[var(--duration-base)] hover:shadow-md' : '',
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->class($classes) }}>
    @if ($title || $subtitle || isset($header))
        <div class="px-6 py-4 border-b border-surface-200 dark:border-ink-700 flex items-center justify-between gap-4">
            <div>
                @if ($title)<h3 class="text-base font-semibold text-ink-900 dark:text-ink-50">{{ $title }}</h3>@endif
                @if ($subtitle)<p class="text-sm text-ink-500 dark:text-ink-400 mt-0.5">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)
                <div class="shrink-0 flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div class="{{ $pads[$padding] ?? $pads['md'] }}">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="px-6 py-3 border-t border-surface-200 bg-surface-50 rounded-b-lg dark:border-ink-700 dark:bg-ink-950">
            {{ $footer }}
        </div>
    @endisset
</div>
