@props([
    'tone' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $tones = [
        'success' => ['rule' => 'border-l-sage-500', 'bg' => 'bg-sage-50', 'text' => 'text-sage-700'],
        'warning' => ['rule' => 'border-l-warning-500', 'bg' => 'bg-warning-50', 'text' => 'text-warning-700'],
        'danger' => ['rule' => 'border-l-danger-500', 'bg' => 'bg-danger-50', 'text' => 'text-danger-700'],
        'info' => ['rule' => 'border-l-ink-400', 'bg' => 'bg-surface-50', 'text' => 'text-ink-800'],
    ];
    $t = $tones[$tone] ?? $tones['info'];
@endphp

<div
    @if ($dismissible) x-data="{ shown: true }" x-show="shown" @endif
    {{ $attributes->class("border-l-4 {$t['rule']} {$t['bg']} {$t['text']} rounded-r-sm px-4 py-3 flex items-start gap-3") }}>
    <div class="flex-1 text-sm">
        @if ($title)<h4 class="font-semibold">{{ $title }}</h4>@endif
        <div class="{{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" @click="shown = false" class="shrink-0 opacity-60 hover:opacity-100 transition-opacity" aria-label="Dismiss">
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.28 3.22a.75.75 0 00-1.06 1.06L8.94 10l-5.72 5.72a.75.75 0 101.06 1.06L10 11.06l5.72 5.72a.75.75 0 101.06-1.06L11.06 10l5.72-5.72a.75.75 0 00-1.06-1.06L10 8.94 4.28 3.22z" clip-rule="evenodd" /></svg>
        </button>
    @endif
</div>
