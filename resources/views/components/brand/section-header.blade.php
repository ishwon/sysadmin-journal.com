@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header class="max-w-2xl">
    @if ($eyebrow)
        <p class="text-xs uppercase tracking-wider font-semibold text-accent-700 dark:text-accent-400">{{ $eyebrow }}</p>
    @endif
    <h2 class="mt-1 text-2xl font-bold tracking-tight text-ink-900 dark:text-ink-50">{{ $title }}</h2>
    @if ($description)
        <p class="mt-2 text-ink-600 dark:text-ink-300">{{ $description }}</p>
    @endif
</header>
