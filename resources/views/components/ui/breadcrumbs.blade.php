@props(['items' => []])

{{-- Mono path breadcrumb: dashboard / media / 2026 --}}
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center gap-1.5 font-mono text-xs tracking-wide">
        @foreach ($items as $i => $item)
            <li class="inline-flex items-center gap-1.5">
                @if ($i > 0)<span class="text-ink-300" aria-hidden="true">/</span>@endif
                @if (!empty($item['href']) && !$loop->last)
                    <a href="{{ $item['href'] }}" class="text-ink-400 hover:text-ink-900 transition-colors">{{ $item['label'] }}</a>
                @else
                    <span class="text-ink-900">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
