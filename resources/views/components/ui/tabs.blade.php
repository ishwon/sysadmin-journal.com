@props([
    'tabs' => [],
    'default' => null,
])

@php
    $initial = $default ?? (array_keys($tabs)[0] ?? '');
@endphp

<div x-data="{ active: @js($initial) }">
    <div class="border-b border-surface-200 dark:border-ink-700">
        <nav class="flex gap-6 -mb-px" aria-label="Tabs">
            @foreach ($tabs as $key => $label)
                <button type="button"
                    @click="active = @js($key)"
                    :class="active === @js($key) ? 'border-accent-500 text-accent-700 dark:text-accent-400' : 'border-transparent text-ink-500 hover:text-ink-800 hover:border-ink-300 dark:text-ink-400 dark:hover:text-ink-100'"
                    class="inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-medium transition-colors">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>
    <div class="pt-6">
        {{ $slot }}
    </div>
</div>
