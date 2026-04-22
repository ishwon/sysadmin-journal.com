@props([
    'name',
    'label' => null,
    'hint' => null,
    'error' => null,
    'type' => 'text',
    'value' => null,
    'prefix' => null,
    'suffix' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = filled($error);
    $inputClasses = collect([
        'w-full rounded-md border bg-white px-3 py-2 text-sm text-ink-900',
        'placeholder:text-ink-400',
        'transition-colors duration-[var(--duration-quick)]',
        'focus:outline-none',
        'disabled:bg-surface-100 disabled:text-ink-400 disabled:cursor-not-allowed',
        'dark:bg-ink-900 dark:text-ink-50 dark:placeholder:text-ink-500',
        $hasError
            ? 'border-danger-500 focus:border-danger-500 focus:ring-4 focus:ring-danger-500/20'
            : 'border-ink-200 focus:border-accent-500 focus:ring-4 focus:ring-accent-500/20 dark:border-ink-700',
        $prefix ? 'rounded-l-none' : '',
        $suffix ? 'rounded-r-none' : '',
    ])->filter()->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-ink-800 dark:text-ink-100 mb-1">
            {{ $label }}
        </label>
    @endif

    @if ($prefix || $suffix)
        <div class="flex">
            @if ($prefix)
                <span class="inline-flex items-center rounded-l-md border border-r-0 border-ink-200 bg-surface-100 px-3 text-sm text-ink-500 dark:bg-ink-800 dark:border-ink-700 dark:text-ink-300">
                    {{ $prefix }}
                </span>
            @endif
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
                @if (!is_null($value)) value="{{ $value }}" @endif
                {{ $attributes->class($inputClasses) }}>
            @if ($suffix)
                <span class="inline-flex items-center rounded-r-md border border-l-0 border-ink-200 bg-surface-100 px-3 text-sm text-ink-500 dark:bg-ink-800 dark:border-ink-700 dark:text-ink-300">
                    {{ $suffix }}
                </span>
            @endif
        </div>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
            @if (!is_null($value)) value="{{ $value }}" @endif
            {{ $attributes->class($inputClasses) }}>
    @endif

    @if ($hasError)
        <p class="mt-1 text-xs text-danger-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
            </svg>
            {{ $error }}
        </p>
    @elseif ($hint)
        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ $hint }}</p>
    @endif
</div>
