@props([
    'name',
    'label' => null,
    'value' => '1',
    'checked' => false,
    'hint' => null,
])

@php $id = $attributes->get('id') ?? $name; @endphp

<label for="{{ $id }}" class="inline-flex items-start gap-2 cursor-pointer">
    <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($checked)
        {{ $attributes->class('mt-0.5 h-4 w-4 rounded-xs border-ink-300 accent-accent-600 focus:shadow-[var(--shadow-focus)] focus:outline-none cursor-pointer') }}>
    <span class="flex-1">
        @if ($label)<span class="block text-sm text-ink-900 select-none">{{ $label }}</span>@endif
        @if ($hint)<span class="block font-mono text-[11px] text-ink-400 mt-0.5">{{ $hint }}</span>@endif
        {{ $slot }}
    </span>
</label>
