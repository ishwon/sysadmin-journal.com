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
        'w-full rounded-sm border bg-white px-3 py-2 text-sm text-ink-900',
        'placeholder:text-ink-300',
        'transition-colors duration-[var(--duration-quick)]',
        'focus:outline-none',
        'disabled:bg-surface-50 disabled:text-ink-400 disabled:cursor-not-allowed',
        $hasError
            ? 'border-danger-500 focus:border-danger-500 focus:shadow-[0_0_0_3px_rgb(177_74_42_/_0.32)]'
            : 'border-ink-200 focus:border-accent-500 focus:shadow-[var(--shadow-focus)]',
        $prefix ? 'rounded-l-none' : '',
        $suffix ? 'rounded-r-none' : '',
    ])->filter()->implode(' ');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-[13px] font-medium text-ink-900 mb-1.5">{{ $label }}</label>
    @endif

    @if ($prefix || $suffix)
        <div class="flex">
            @if ($prefix)
                <span class="inline-flex items-center rounded-l-sm border border-r-0 border-ink-200 bg-surface-50 px-3 font-mono text-xs text-ink-500">{{ $prefix }}</span>
            @endif
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" @if (!is_null($value)) value="{{ $value }}" @endif {{ $attributes->class($inputClasses) }}>
            @if ($suffix)
                <span class="inline-flex items-center rounded-r-sm border border-l-0 border-ink-200 bg-surface-50 px-3 font-mono text-xs text-ink-500">{{ $suffix }}</span>
            @endif
        </div>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" @if (!is_null($value)) value="{{ $value }}" @endif {{ $attributes->class($inputClasses) }}>
    @endif

    @if ($hasError)
        <p class="mt-1.5 text-xs text-danger-600">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 font-mono text-[11px] text-ink-400">{{ $hint }}</p>
    @endif
</div>
