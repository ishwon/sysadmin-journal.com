@props([
    'tone' => 'success',
    'title' => null,
    'message' => null,
    'on' => null,
])

@php
    $tones = [
        'success' => 'bg-white border-accent-500 text-ink-900 dark:bg-ink-900 dark:text-ink-50',
        'warning' => 'bg-white border-warning-500 text-ink-900 dark:bg-ink-900 dark:text-ink-50',
        'danger' => 'bg-white border-danger-500 text-ink-900 dark:bg-ink-900 dark:text-ink-50',
        'info' => 'bg-white border-ink-400 text-ink-900 dark:bg-ink-900 dark:text-ink-50',
    ];

    $iconTones = [
        'success' => 'text-accent-600 bg-accent-50 dark:bg-accent-900/40 dark:text-accent-300',
        'warning' => 'text-warning-600 bg-warning-50 dark:bg-warning-900/40 dark:text-warning-300',
        'danger' => 'text-danger-600 bg-danger-50 dark:bg-danger-900/40 dark:text-danger-300',
        'info' => 'text-ink-500 bg-surface-100 dark:bg-ink-800 dark:text-ink-300',
    ];

    $icons = [
        'success' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
        'warning' => '<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625l6.28-10.875zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>',
        'danger' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>',
        'info' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>',
    ];
@endphp

<div
    x-data="{ shown: false }"
    @if ($on) x-on:{{ $on }}.window="shown = true; setTimeout(() => shown = false, 4500)" @endif
    x-show="shown"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    {{ $attributes->class("pointer-events-auto rounded-lg border-l-4 shadow-lg p-4 flex items-start gap-3 min-w-80 max-w-md " . ($tones[$tone] ?? $tones['info'])) }}>
    <span class="shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-full {{ $iconTones[$tone] ?? $iconTones['info'] }}">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">{!! $icons[$tone] ?? $icons['info'] !!}</svg>
    </span>
    <div class="flex-1 text-sm">
        @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
        @if ($message)<p class="{{ $title ? 'mt-0.5' : '' }} text-ink-600 dark:text-ink-300">{{ $message }}</p>@endif
        {{ $slot }}
    </div>
    <button type="button" @click="shown = false" class="shrink-0 text-ink-400 hover:text-ink-700 dark:hover:text-ink-100" aria-label="Dismiss">
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.28 3.22a.75.75 0 00-1.06 1.06L8.94 10l-5.72 5.72a.75.75 0 101.06 1.06L10 11.06l5.72 5.72a.75.75 0 101.06-1.06L11.06 10l5.72-5.72a.75.75 0 00-1.06-1.06L10 8.94 4.28 3.22z" clip-rule="evenodd"/></svg>
    </button>
</div>
