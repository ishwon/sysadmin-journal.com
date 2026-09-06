@props([
    'label',
    'value',
    'change' => null,
    'trend' => null,
    'icon' => null,
])

{{-- Hairline stat cell: sits in a row, separated by a right rule. --}}
<div {{ $attributes->class('py-6 pr-6 mr-6 border-r border-ink-100 last:border-r-0 last:mr-0 last:pr-0') }}>
    <p class="font-mono text-[11px] lowercase tracking-wider text-ink-400">{{ $label }}</p>
    <p class="mt-2 font-display text-4xl leading-none text-ink-900">{{ $value }}</p>
    @if ($change)
        <p class="mt-2 font-mono text-[11px] {{ $trend === 'down' ? 'text-danger-600' : ($trend === 'up' ? 'text-sage-500' : 'text-ink-400') }}">{{ $change }}</p>
    @endif
</div>
