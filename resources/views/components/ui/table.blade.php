@props([
    'columns' => [],
])

<div {{ $attributes->class('overflow-hidden rounded-lg border border-surface-200 bg-white dark:bg-ink-900 dark:border-ink-700') }}>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-surface-100 dark:bg-ink-800">
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ink-500 dark:text-ink-300 whitespace-nowrap">
                            {{ is_array($column) ? $column['label'] : $column }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-200 dark:divide-ink-800 bg-white dark:bg-ink-900">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @isset($footer)
        <div class="px-4 py-3 border-t border-surface-200 bg-surface-50 dark:border-ink-700 dark:bg-ink-950">
            {{ $footer }}
        </div>
    @endisset
</div>
