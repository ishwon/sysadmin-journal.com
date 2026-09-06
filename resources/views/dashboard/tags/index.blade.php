@extends('layouts.dashboard')

@section('title', 'Tags')
@section('page-title', 'Tags')

@section('actions')
<x-ui.button href="{{ route('dashboard.tags.create') }}" variant="primary" size="sm">New tag</x-ui.button>
@endsection

@section('content')
<x-ui.table :columns="['name', 'slug', 'posts', '']">
    @forelse ($tags as $tag)
        <tr>
            <td><a href="{{ route('dashboard.tags.edit', $tag) }}" class="font-display text-lg text-ink-900 hover:text-accent-700 transition-colors">{{ $tag->name }}</a></td>
            <td><code class="font-mono text-xs bg-surface-50 px-1.5 py-0.5 rounded-xs text-ink-700">/tag/{{ $tag->slug }}</code></td>
            <td class="meta">{{ $tag->posts_count }}</td>
            <td class="text-right whitespace-nowrap meta">
                <a href="{{ route('dashboard.tags.edit', $tag) }}" class="link-rise">edit</a>
                <span class="text-ink-200"> · </span>
                <a href="/tag/{{ $tag->slug }}" target="_blank" class="link-rise">view</a>
                <span class="text-ink-200"> · </span>
                <form method="POST" action="{{ route('dashboard.tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-danger-500 hover:text-danger-700 font-mono">delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="py-10">
            <x-ui.empty-state title="No tags yet" description="Tags group posts by topic. Every post gets at least one.">
                <x-slot:action><x-ui.button href="{{ route('dashboard.tags.create') }}" variant="primary">Create tag</x-ui.button></x-slot:action>
            </x-ui.empty-state>
        </td></tr>
    @endforelse
    @if ($tags->hasPages())
        <x-slot:footer>{{ $tags->withQueryString()->links() }}</x-slot:footer>
    @endif
</x-ui.table>
@endsection
