@props(['items' => []])

<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center gap-2 text-sm">
        @foreach ($items as $i => $item)
            <li class="inline-flex items-center gap-2">
                @if ($i > 0)
                    <svg class="w-4 h-4 text-ink-300 dark:text-ink-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.22 14.78a.75.75 0 001.06 0l4.25-4.25a.75.75 0 000-1.06L8.28 5.22a.75.75 0 00-1.06 1.06L10.94 10l-3.72 3.72a.75.75 0 000 1.06z" clip-rule="evenodd" />
                    </svg>
                @endif
                @if (!empty($item['href']) && !$loop->last)
                    <a href="{{ $item['href'] }}" class="text-ink-500 hover:text-ink-800 dark:text-ink-400 dark:hover:text-ink-100 transition-colors">{{ $item['label'] }}</a>
                @else
                    <span class="font-medium text-ink-900 dark:text-ink-50">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
