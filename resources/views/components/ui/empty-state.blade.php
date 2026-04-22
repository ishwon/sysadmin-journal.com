@props([
    'title',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->class('flex flex-col items-center justify-center text-center px-6 py-12 rounded-lg border-2 border-dashed border-surface-300 bg-surface-50 dark:bg-ink-900 dark:border-ink-700') }}>
    <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-surface-200 text-ink-500 dark:bg-ink-800 dark:text-ink-300">
        @if ($icon)
            {!! $icon !!}
        @else
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        @endif
    </div>
    <h3 class="mt-4 text-base font-semibold text-ink-900 dark:text-ink-50">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-400 max-w-sm">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
