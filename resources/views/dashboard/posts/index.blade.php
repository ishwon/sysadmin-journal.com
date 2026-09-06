@extends('layouts.dashboard')

@section('title', 'Posts')
@section('page-title', 'Posts')

@section('page-aside')
<div class="flex gap-4 meta">
    <a href="{{ route('dashboard.posts.index') }}" class="{{ request('status') ? 'text-ink-400' : 'text-ink-900 border-b border-ink-900' }} pb-0.5">all</a>
    <a href="{{ route('dashboard.posts.index', ['status' => 'published']) }}" class="{{ request('status') === 'published' ? 'text-ink-900 border-b border-ink-900' : 'text-ink-400' }} pb-0.5">published</a>
    <a href="{{ route('dashboard.posts.index', ['status' => 'draft']) }}" class="{{ request('status') === 'draft' ? 'text-ink-900 border-b border-ink-900' : 'text-ink-400' }} pb-0.5">drafts</a>
</div>
@endsection

@section('actions')
<x-ui.button href="{{ route('dashboard.posts.create') }}" variant="primary" size="sm">New post</x-ui.button>
@endsection

@section('content')
<x-ui.table :columns="['title', 'status', 'date', '']">
    @forelse ($posts as $post)
        <tr>
            <td>
                <a href="{{ route('dashboard.posts.edit', $post) }}" class="font-display text-lg text-ink-900 hover:text-accent-700 transition-colors">{{ $post->title }}</a>
                <p class="meta mt-0.5">/{{ $post->slug }}</p>
            </td>
            <td><x-ui.badge :tone="$post->status === 'published' ? 'success' : 'neutral'">{{ $post->status }}</x-ui.badge></td>
            <td class="meta whitespace-nowrap">{{ ($post->published_at ?? $post->created_at)->format('j M Y') }}</td>
            <td class="text-right whitespace-nowrap meta">
                <a href="{{ route('dashboard.posts.edit', $post) }}" class="link-rise">edit</a>
                <span class="text-ink-200"> · </span>
                <a href="{{ route('dashboard.posts.show', $post) }}" target="_blank" class="link-rise">preview</a>
                <span class="text-ink-200"> · </span>
                <a href="/{{ $post->slug }}" target="_blank" class="link-rise">view</a>
                <span class="text-ink-200"> · </span>
                <form method="POST" action="{{ route('dashboard.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-danger-500 hover:text-danger-700 font-mono">delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="py-10">
                <x-ui.empty-state title="No posts yet" description="Write your first post to see it here.">
                    <x-slot:action>
                        <x-ui.button href="{{ route('dashboard.posts.create') }}" variant="primary">Create post</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </td>
        </tr>
    @endforelse
    @if ($posts->hasPages())
        <x-slot:footer>{{ $posts->withQueryString()->links() }}</x-slot:footer>
    @endif
</x-ui.table>
@endsection
