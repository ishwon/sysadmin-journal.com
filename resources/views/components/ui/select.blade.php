@props([
    'name',
    'label' => null,
    'hint' => null,
    'error' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = filled($error);
    $classes = collect([
        'w-full appearance-none rounded-md border bg-white px-3 py-2 pr-10 text-sm text-ink-900',
        'transition-colors duration-[var(--duration-quick)]',
        'focus:outline-none',
        'disabled:bg-surface-100 disabled:text-ink-400 disabled:cursor-not-allowed',
        'dark:bg-ink-900 dark:text-ink-50',
        $hasError
            ? 'border-danger-500 focus:border-danger-500 focus:ring-4 focus:ring-danger-500/20'
            : 'border-ink-200 focus:border-accent-500 focus:ring-4 focus:ring-accent-500/20 dark:border-ink-700',
    ])->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-ink-800 dark:text-ink-100 mb-1">
            {{ $label }}
        </label>
    @endif
    <div class="relative">
        <select name="{{ $name }}" id="{{ $id }}" {{ $attributes->class($classes) }}>
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $value => $label)
                <option value="{{ $value }}" @selected($selected == $value)>{{ $label }}</option>
            @endforeach
            {{ $slot }}
        </select>
        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-ink-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.38a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </div>
    @if ($hasError)
        <p class="mt-1 text-xs text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ $hint }}</p>
    @endif
</div>
