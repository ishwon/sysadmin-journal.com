@props([
    'name',
    'label' => null,
    'value',
    'checked' => false,
])

@php $id = $attributes->get('id') ?? "{$name}_{$value}"; @endphp

<label for="{{ $id }}" class="inline-flex items-center gap-2 cursor-pointer">
    <input type="radio" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($checked)
        {{ $attributes->class('h-4 w-4 border-ink-300 accent-accent-600 focus:shadow-[var(--shadow-focus)] focus:outline-none cursor-pointer') }}>
    @if ($label)<span class="text-sm text-ink-900 select-none">{{ $label }}</span>@endif
    {{ $slot }}
</label>
