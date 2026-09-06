@props([
    'title',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->class('flex flex-col items-center justify-center text-center px-6 py-16 border border-dashed border-ink-200 rounded-md') }}>
    <p class="eyebrow">/empty/</p>
    <h3 class="mt-2 font-display text-2xl text-ink-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 font-serif text-base text-ink-500 max-w-sm text-pretty">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-6">{{ $action }}</div>
    @endisset
</div>
