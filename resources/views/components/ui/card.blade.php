@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'md',
    'hoverable' => false,
])

@php
    /* A "card" is a hairline-bordered region, not a chrome box. No shadow at rest. */
    $pads = ['none' => '', 'sm' => 'p-4', 'md' => 'p-6', 'lg' => 'p-8'];

    $classes = collect([
        'rounded-md bg-white border border-ink-100',
        $hoverable ? 'transition-shadow duration-[var(--duration-base)] hover:shadow-md' : '',
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->class($classes) }}>
    @if ($title || $subtitle || isset($header))
        <div class="px-6 py-4 border-b border-ink-100 flex items-center justify-between gap-4">
            <div>
                @if ($title)<h3 class="eyebrow">/{{ Str::slug($title) }}/</h3>@endif
                @if ($subtitle)<p class="text-sm text-ink-500 mt-1">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)
                <div class="shrink-0 flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div class="{{ $pads[$padding] ?? $pads['md'] }}">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="px-6 py-3 border-t border-ink-100 bg-surface-50 rounded-b-md">
            {{ $footer }}
        </div>
    @endisset
</div>
