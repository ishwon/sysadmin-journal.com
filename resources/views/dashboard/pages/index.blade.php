@extends('layouts.dashboard')

@section('title', 'Pages')
@section('page-title', 'Pages')

@section('actions')
<x-ui.button href="{{ route('dashboard.pages.create') }}" variant="primary" size="sm">New page</x-ui.button>
@endsection

@section('content')
<x-ui.table :columns="['title', 'status', 'updated', '']">
    @forelse ($pages as $page)
        <tr>
            <td>
                <a href="{{ route('dashboard.pages.edit', $page) }}" class="font-display text-lg text-ink-900 hover:text-accent-700 transition-colors">{{ $page->title }}</a>
                <p class="meta mt-0.5">/{{ $page->slug }}</p>
            </td>
            <td><x-ui.badge :tone="$page->status === 'published' ? 'success' : 'neutral'">{{ $page->status }}</x-ui.badge></td>
            <td class="meta whitespace-nowrap">{{ $page->updated_at->format('j M Y') }}</td>
            <td class="text-right whitespace-nowrap meta">
                <a href="{{ route('dashboard.pages.edit', $page) }}" class="link-rise">edit</a>
                <span class="text-ink-200"> · </span>
                <a href="/{{ $page->slug }}" target="_blank" class="link-rise">view</a>
                <span class="text-ink-200"> · </span>
                <form method="POST" action="{{ route('dashboard.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-danger-500 hover:text-danger-700 font-mono">delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="py-10">
            <x-ui.empty-state title="No pages yet" description="Pages are static content (About, Contact, etc.) not part of the chronological feed.">
                <x-slot:action><x-ui.button href="{{ route('dashboard.pages.create') }}" variant="primary">Create page</x-ui.button></x-slot:action>
            </x-ui.empty-state>
        </td></tr>
    @endforelse
    @if ($pages->hasPages())
        <x-slot:footer>{{ $pages->withQueryString()->links() }}</x-slot:footer>
    @endif
</x-ui.table>
@endsection
