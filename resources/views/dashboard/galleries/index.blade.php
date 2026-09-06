@extends('layouts.dashboard')

@section('title', 'Galleries')
@section('page-title', 'Galleries')

@section('actions')
<x-ui.button href="{{ route('dashboard.galleries.create') }}" variant="primary" size="sm">New gallery</x-ui.button>
@endsection

@section('content')
<x-ui.table :columns="['title', 'images', 'shortcode', '']">
    @forelse ($galleries as $gallery)
        <tr>
            <td><a href="{{ route('dashboard.galleries.edit', $gallery) }}" class="font-display text-lg text-ink-900 hover:text-accent-700 transition-colors">{{ $gallery->title }}</a></td>
            <td class="meta">{{ $gallery->images_count }}</td>
            <td><code class="font-mono text-xs bg-surface-50 px-1.5 py-0.5 rounded-xs text-ink-700">[gallery:{{ $gallery->slug }}]</code></td>
            <td class="text-right whitespace-nowrap meta">
                <a href="{{ route('dashboard.galleries.edit', $gallery) }}" class="link-rise">edit</a>
                <span class="text-ink-200"> · </span>
                <a href="/gallery/{{ $gallery->slug }}" target="_blank" class="link-rise">view</a>
                <span class="text-ink-200"> · </span>
                <form method="POST" action="{{ route('dashboard.galleries.destroy', $gallery) }}" onsubmit="return confirm('Delete this gallery?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-danger-500 hover:text-danger-700 font-mono">delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="py-10">
            <x-ui.empty-state title="No galleries yet" description="Galleries group photos into reusable collections. Embed them in posts via shortcode.">
                <x-slot:action><x-ui.button href="{{ route('dashboard.galleries.create') }}" variant="primary">Create gallery</x-ui.button></x-slot:action>
            </x-ui.empty-state>
        </td></tr>
    @endforelse
    @if ($galleries->hasPages())
        <x-slot:footer>{{ $galleries->withQueryString()->links() }}</x-slot:footer>
    @endif
</x-ui.table>
@endsection
