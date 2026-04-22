@props([
    'name',
    'label' => null,
    'value',
    'checked' => false,
])

@php
    $id = $attributes->get('id') ?? "{$name}_{$value}";
@endphp

<label for="{{ $id }}" class="inline-flex items-center gap-2 cursor-pointer">
    <input type="radio" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($checked)
        {{ $attributes->class('h-4 w-4 border-ink-300 text-accent-600 focus:ring-4 focus:ring-accent-500/20 focus:ring-offset-0 cursor-pointer dark:bg-ink-900 dark:border-ink-600') }}>
    @if ($label)
        <span class="text-sm text-ink-800 dark:text-ink-100 select-none">{{ $label }}</span>
    @endif
    {{ $slot }}
</label>
