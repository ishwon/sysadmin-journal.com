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
        'w-full rounded-sm border bg-white px-3 py-2 text-sm text-ink-900',
        'placeholder:text-ink-300',
        'transition-colors duration-[var(--duration-quick)] focus:outline-none resize-y',
        'disabled:bg-surface-50 disabled:text-ink-400 disabled:cursor-not-allowed',
        $mono ? 'font-mono leading-relaxed' : '',
        $hasError
            ? 'border-danger-500 focus:border-danger-500 focus:shadow-[0_0_0_3px_rgb(177_74_42_/_0.32)]'
            : 'border-ink-200 focus:border-accent-500 focus:shadow-[var(--shadow-focus)]',
    ])->filter()->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-[13px] font-medium text-ink-900 mb-1.5">{{ $label }}</label>
    @endif
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" {{ $attributes->class($classes) }}>{{ $slot }}</textarea>
    @if ($hasError)
        <p class="mt-1.5 text-xs text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 font-mono text-[11px] text-ink-400">{{ $hint }}</p>
    @endif
</div>
