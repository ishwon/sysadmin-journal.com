@extends('layouts.dashboard')

@section('title', 'Posts')

@section('actions')
<x-ui.button href="{{ route('dashboard.posts.create') }}" variant="primary" size="sm">
    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
    New post
</x-ui.button>
@endsection

@section('content')
<x-ui.table :columns="['Title', 'Status', 'Date', '']">
    @forelse ($posts as $post)
        <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/60 transition-colors">
            <td class="px-4 py-3">
                <p class="font-medium text-ink-900 dark:text-ink-50">{{ $post->title }}</p>
                <p class="mt-0.5 text-xs font-mono text-ink-500 dark:text-ink-400">/{{ $post->slug }}</p>
            </td>
            <td class="px-4 py-3">
                <x-ui.badge :tone="$post->status === 'published' ? 'success' : 'warning'" dot>
                    {{ ucfirst($post->status) }}
                </x-ui.badge>
            </td>
            <td class="px-4 py-3 text-sm text-ink-500 dark:text-ink-400 whitespace-nowrap">
                {{ $post->published_at?->format('d M Y') ?? $post->created_at->format('d M Y') }}
            </td>
            <td class="px-4 py-3 text-right">
                <x-ui.dropdown>
                    <x-slot:trigger>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-ink-500 hover:bg-surface-100 dark:text-ink-400 dark:hover:bg-ink-800 transition-colors" aria-label="Row actions">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="{{ route('dashboard.posts.edit', $post) }}">Edit</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="{{ route('dashboard.posts.show', $post) }}" target="_blank">Preview</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="/{{ $post->slug }}" target="_blank">View public</x-ui.dropdown-item>
                    <div class="my-1 border-t border-surface-200 dark:border-ink-700"></div>
                    <form method="POST" action="{{ route('dashboard.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')" class="block">
                        @csrf
                        @method('DELETE')
                        <x-ui.dropdown-item type="submit" tone="danger">Delete</x-ui.dropdown-item>
                    </form>
                </x-ui.dropdown>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-4 py-10">
                <x-ui.empty-state title="No posts yet" description="Write your first post to see it here.">
                    <x-slot:action>
                        <x-ui.button href="{{ route('dashboard.posts.create') }}" variant="primary">Create post</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </td>
        </tr>
    @endforelse
    @if ($posts->hasPages())
        <x-slot:footer>
            {{ $posts->withQueryString()->links() }}
        </x-slot:footer>
    @endif
</x-ui.table>
@endsection
