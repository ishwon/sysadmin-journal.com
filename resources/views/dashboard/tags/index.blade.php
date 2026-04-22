@extends('layouts.dashboard')

@section('title', 'Tags')

@section('actions')
<x-ui.button href="{{ route('dashboard.tags.create') }}" variant="primary" size="sm">
    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
    New tag
</x-ui.button>
@endsection

@section('content')
<x-ui.table :columns="['Name', 'Slug', 'Posts', '']">
    @forelse ($tags as $tag)
        <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/60 transition-colors">
            <td class="px-4 py-3 font-medium text-ink-900 dark:text-ink-50">{{ $tag->name }}</td>
            <td class="px-4 py-3">
                <code class="inline-flex items-center gap-1 rounded bg-ink-50 dark:bg-ink-800 px-2 py-0.5 font-mono text-xs text-ink-800 dark:text-ink-100">/tag/{{ $tag->slug }}</code>
            </td>
            <td class="px-4 py-3 text-sm text-ink-500 dark:text-ink-400">{{ $tag->posts_count }}</td>
            <td class="px-4 py-3 text-right">
                <x-ui.dropdown>
                    <x-slot:trigger>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-ink-500 hover:bg-surface-100 dark:text-ink-400 dark:hover:bg-ink-800" aria-label="Row actions">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="{{ route('dashboard.tags.edit', $tag) }}">Edit</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="/tag/{{ $tag->slug }}" target="_blank">View public</x-ui.dropdown-item>
                    <div class="my-1 border-t border-surface-200 dark:border-ink-700"></div>
                    <form method="POST" action="{{ route('dashboard.tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag?')" class="block">
                        @csrf @method('DELETE')
                        <x-ui.dropdown-item type="submit" tone="danger">Delete</x-ui.dropdown-item>
                    </form>
                </x-ui.dropdown>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-4 py-10">
                <x-ui.empty-state title="No tags yet" description="Tags group posts by topic. Every post gets at least one.">
                    <x-slot:action>
                        <x-ui.button href="{{ route('dashboard.tags.create') }}" variant="primary">Create tag</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </td>
        </tr>
    @endforelse
    @if ($tags->hasPages())
        <x-slot:footer>{{ $tags->withQueryString()->links() }}</x-slot:footer>
    @endif
</x-ui.table>
@endsection
