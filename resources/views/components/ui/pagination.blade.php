@props([
    'current' => 1,
    'total' => 1,
    'onPage' => null,
])

@php
    $pages = range(max(1, $current - 2), min($total, $current + 2));
@endphp

<nav class="flex items-center justify-between gap-4" aria-label="Pagination">
    <p class="text-sm text-ink-500 dark:text-ink-400">
        Page <span class="font-medium text-ink-800 dark:text-ink-100">{{ $current }}</span>
        of <span class="font-medium text-ink-800 dark:text-ink-100">{{ $total }}</span>
    </p>
    <div class="inline-flex items-center gap-1">
        <button type="button" @disabled($current <= 1)
            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-ink-200 bg-white text-ink-600 hover:bg-surface-100 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-ink-900 dark:border-ink-700 dark:text-ink-300 dark:hover:bg-ink-800">
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 010 1.06L9.06 10l3.73 3.71a.75.75 0 11-1.06 1.06l-4.25-4.24a.75.75 0 010-1.06l4.25-4.24a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
        </button>
        @foreach ($pages as $page)
            <button type="button"
                class="inline-flex h-8 min-w-8 px-2 items-center justify-center rounded-md text-sm font-medium transition-colors
                    {{ $page === $current
                        ? 'bg-accent-600 text-white'
                        : 'text-ink-700 hover:bg-surface-100 dark:text-ink-200 dark:hover:bg-ink-800' }}">
                {{ $page }}
            </button>
        @endforeach
        <button type="button" @disabled($current >= $total)
            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-ink-200 bg-white text-ink-600 hover:bg-surface-100 disabled:opacity-40 disabled:cursor-not-allowed dark:bg-ink-900 dark:border-ink-700 dark:text-ink-300 dark:hover:bg-ink-800">
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.24a.75.75 0 010 1.06l-4.25 4.24a.75.75 0 01-1.06 0z" clip-rule="evenodd"/></svg>
        </button>
    </div>
</nav>
