@props([
    'label',
    'value',
    'change' => null,
    'trend' => null,
    'icon' => null,
])

@php
    $trendTones = [
        'up' => 'text-accent-700 bg-accent-50 dark:text-accent-300 dark:bg-accent-900/30',
        'down' => 'text-danger-700 bg-danger-50 dark:text-danger-300 dark:bg-danger-900/30',
        'flat' => 'text-ink-600 bg-surface-100 dark:text-ink-300 dark:bg-ink-800',
    ];
@endphp

<div {{ $attributes->class('rounded-lg bg-white border border-surface-200 p-6 shadow-sm dark:bg-ink-900 dark:border-ink-700') }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-wide text-ink-500 dark:text-ink-400 font-medium">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-ink-900 dark:text-ink-50">{{ $value }}</p>
        </div>
        @if ($icon)
            <span class="shrink-0 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-ink-50 text-ink-600 dark:bg-ink-800 dark:text-ink-300">
                {!! $icon !!}
            </span>
        @endif
    </div>
    @if ($change || $trend)
        <div class="mt-4 flex items-center gap-2">
            @if ($trend)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $trendTones[$trend] ?? $trendTones['flat'] }}">
                    @if ($trend === 'up')
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3l6 8h-4v6H8v-6H4l6-8z" /></svg>
                    @elseif ($trend === 'down')
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10 17l-6-8h4V3h4v6h4l-6 8z" /></svg>
                    @else
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M4 10h12" stroke="currentColor" stroke-width="2" /></svg>
                    @endif
                    {{ $change }}
                </span>
            @endif
            @isset($period)
                <span class="text-xs text-ink-500 dark:text-ink-400">{{ $period }}</span>
            @endisset
        </div>
    @endif
</div>
