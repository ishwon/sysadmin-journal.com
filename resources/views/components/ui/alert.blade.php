@props([
    'tone' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $tones = [
        'success' => [
            'border' => 'border-l-accent-500',
            'bg' => 'bg-accent-50 dark:bg-accent-900/20',
            'text' => 'text-accent-900 dark:text-accent-100',
            'iconColor' => 'text-accent-600 dark:text-accent-400',
            'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />',
        ],
        'warning' => [
            'border' => 'border-l-warning-500',
            'bg' => 'bg-warning-50 dark:bg-warning-900/20',
            'text' => 'text-warning-900 dark:text-warning-100',
            'iconColor' => 'text-warning-600 dark:text-warning-400',
            'icon' => '<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625l6.28-10.875zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />',
        ],
        'danger' => [
            'border' => 'border-l-danger-500',
            'bg' => 'bg-danger-50 dark:bg-danger-900/20',
            'text' => 'text-danger-900 dark:text-danger-100',
            'iconColor' => 'text-danger-600 dark:text-danger-400',
            'icon' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />',
        ],
        'info' => [
            'border' => 'border-l-ink-400',
            'bg' => 'bg-surface-100 dark:bg-ink-800',
            'text' => 'text-ink-800 dark:text-ink-100',
            'iconColor' => 'text-ink-500 dark:text-ink-300',
            'icon' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />',
        ],
    ];

    $t = $tones[$tone] ?? $tones['info'];
@endphp

<div
    @if ($dismissible) x-data="{ shown: true }" x-show="shown" @endif
    {{ $attributes->class("border-l-4 {$t['border']} {$t['bg']} {$t['text']} rounded-r-md p-4 flex items-start gap-3") }}>
    <svg class="{{ $t['iconColor'] }} w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        {!! $t['icon'] !!}
    </svg>
    <div class="flex-1 text-sm">
        @if ($title)<h4 class="font-semibold">{{ $title }}</h4>@endif
        <div class="{{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" @click="shown = false" class="shrink-0 opacity-60 hover:opacity-100 transition-opacity" aria-label="Dismiss">
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.28 3.22a.75.75 0 00-1.06 1.06L8.94 10l-5.72 5.72a.75.75 0 101.06 1.06L10 11.06l5.72 5.72a.75.75 0 101.06-1.06L11.06 10l5.72-5.72a.75.75 0 00-1.06-1.06L10 8.94 4.28 3.22z" clip-rule="evenodd" />
            </svg>
        </button>
    @endif
</div>
