@props([
    'name',
    'label' => null,
    'hint' => null,
    'error' => null,
    'rows' => 4,
    'mono' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = filled($error);
    $classes = collect([
        'w-full rounded-md border bg-white px-3 py-2 text-sm text-ink-900',
        'placeholder:text-ink-400',
        'transition-colors duration-[var(--duration-quick)]',
        'focus:outline-none resize-y',
        'disabled:bg-surface-100 disabled:text-ink-400 disabled:cursor-not-allowed',
        'dark:bg-ink-900 dark:text-ink-50 dark:placeholder:text-ink-500',
        $mono ? 'font-mono' : '',
        $hasError
            ? 'border-danger-500 focus:border-danger-500 focus:ring-4 focus:ring-danger-500/20'
            : 'border-ink-200 focus:border-accent-500 focus:ring-4 focus:ring-accent-500/20 dark:border-ink-700',
    ])->filter()->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-ink-800 dark:text-ink-100 mb-1">
            {{ $label }}
        </label>
    @endif
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" {{ $attributes->class($classes) }}>{{ $slot }}</textarea>
    @if ($hasError)
        <p class="mt-1 text-xs text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ $hint }}</p>
    @endif
</div>
