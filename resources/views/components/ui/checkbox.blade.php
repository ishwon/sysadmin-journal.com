@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => false,
    'hint' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
@endphp

<label for="{{ $id }}" class="inline-flex items-start gap-2 cursor-pointer group">
    <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($checked)
        {{ $attributes->class('mt-0.5 h-4 w-4 rounded border-ink-300 text-accent-600 focus:ring-4 focus:ring-accent-500/20 focus:ring-offset-0 transition-colors cursor-pointer dark:bg-ink-900 dark:border-ink-600') }}>
    <span class="flex-1">
        @if ($label)
            <span class="block text-sm text-ink-800 dark:text-ink-100 select-none">{{ $label }}</span>
        @endif
        @if ($hint)
            <span class="block text-xs text-ink-500 dark:text-ink-400 mt-0.5">{{ $hint }}</span>
        @endif
        {{ $slot }}
    </span>
</label>
