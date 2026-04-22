@props([
    'align' => 'right',
    'width' => '48',
])

@php
    $aligns = [
        'left' => 'left-0 origin-top-left',
        'right' => 'right-0 origin-top-right',
    ];
    $widths = [
        '48' => 'w-48',
        '56' => 'w-56',
        '64' => 'w-64',
    ];
@endphp

<div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <div @click="open = !open">
        {{ $trigger }}
    </div>
    <div x-show="open"
        x-transition:enter="transition ease-[var(--ease-brand)] duration-[var(--duration-quick)]"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="absolute z-40 mt-2 {{ $aligns[$align] ?? $aligns['right'] }} {{ $widths[$width] ?? $widths['48'] }} rounded-md bg-white shadow-lg border border-surface-200 py-1 dark:bg-ink-900 dark:border-ink-700">
        {{ $slot }}
    </div>
</div>
