@props([
    'columns' => [],
])

{{-- Hairline table: no chrome box, mono lowercase column labels. --}}
<div {{ $attributes->class('overflow-x-auto') }}>
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col" class="py-3 first:pl-0 px-4 text-left font-mono text-[11px] font-medium lowercase tracking-wide text-ink-400 whitespace-nowrap border-b border-ink-100">
                        {{ is_array($column) ? $column['label'] : $column }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="[&_tr]:border-b [&_tr]:border-surface-50 [&_tr:hover]:bg-surface-50 [&_td]:py-3.5 [&_td]:px-4 [&_td:first-child]:pl-0 [&_td:last-child]:pr-0">
            {{ $slot }}
        </tbody>
    </table>
    @isset($footer)
        <div class="pt-4 meta">
            {{ $footer }}
        </div>
    @endisset
</div>
