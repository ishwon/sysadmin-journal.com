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
        'w-full appearance-none rounded-sm border bg-white px-3 py-2 pr-10 text-sm text-ink-900',
        'transition-colors duration-[var(--duration-quick)] focus:outline-none',
        'disabled:bg-surface-50 disabled:text-ink-400 disabled:cursor-not-allowed',
        $hasError
            ? 'border-danger-500 focus:border-danger-500 focus:shadow-[0_0_0_3px_rgb(177_74_42_/_0.32)]'
            : 'border-ink-200 focus:border-accent-500 focus:shadow-[var(--shadow-focus)]',
    ])->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-[13px] font-medium text-ink-900 mb-1.5">{{ $label }}</label>
    @endif
    <div class="relative">
        <select name="{{ $name }}" id="{{ $id }}" {{ $attributes->class($classes) }}>
            @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
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
        <p class="mt-1.5 text-xs text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 font-mono text-[11px] text-ink-400">{{ $hint }}</p>
    @endif
</div>
