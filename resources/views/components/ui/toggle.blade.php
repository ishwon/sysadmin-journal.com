@props([
    'name',
    'label' => null,
    'hint' => null,
    'checked' => false,
    'value' => '1',
])

@php
    $id = $attributes->get('id') ?? $name;
@endphp

<label for="{{ $id }}" class="inline-flex items-start gap-3 cursor-pointer group">
    <span x-data="{ on: {{ $checked ? 'true' : 'false' }} }"
          class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors duration-[var(--duration-quick)] ease-[var(--ease-brand)] focus-within:shadow-[var(--shadow-focus)]"
          :class="on ? 'bg-accent-500' : 'bg-ink-300 dark:bg-ink-700'">
        <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" x-model="on"
            class="sr-only peer" {{ $checked ? 'checked' : '' }} {{ $attributes }}>
        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-[var(--duration-quick)] ease-[var(--ease-brand)]"
              :class="on ? 'translate-x-5' : 'translate-x-0'"></span>
    </span>
    @if ($label || $hint)
        <span class="flex-1">
            @if ($label)
                <span class="block text-sm font-medium text-ink-800 dark:text-ink-100 select-none">{{ $label }}</span>
            @endif
            @if ($hint)
                <span class="block text-xs text-ink-500 dark:text-ink-400 mt-0.5">{{ $hint }}</span>
            @endif
        </span>
    @endif
</label>
