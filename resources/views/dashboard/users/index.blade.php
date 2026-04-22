@extends('layouts.dashboard')

@section('title', 'Users')

@section('actions')
<x-ui.button href="{{ route('dashboard.users.create') }}" variant="primary" size="sm">
    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
    New user
</x-ui.button>
@endsection

@section('content')
<x-ui.table :columns="['Name', 'Email', 'Posts', '']">
    @forelse ($users as $user)
        <tr class="hover:bg-surface-50 dark:hover:bg-ink-800/60 transition-colors">
            <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                    @if ($user->profile_image)
                        <img class="h-8 w-8 rounded-full ring-2 ring-white dark:ring-ink-900" src="{{ $user->profile_image }}" alt="">
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-ink-800 text-white text-xs font-semibold">
                            {{ Str::upper(Str::substr($user->name, 0, 2)) }}
                        </span>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-ink-900 dark:text-ink-50">{{ $user->name }}</p>
                        <p class="text-xs font-mono text-ink-500 dark:text-ink-400">{{ $user->slug }}</p>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3 text-sm text-ink-500 dark:text-ink-400">{{ $user->email }}</td>
            <td class="px-4 py-3 text-sm text-ink-500 dark:text-ink-400">{{ $user->posts_count }}</td>
            <td class="px-4 py-3 text-right">
                <x-ui.dropdown>
                    <x-slot:trigger>
                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-ink-500 hover:bg-surface-100 dark:text-ink-400 dark:hover:bg-ink-800" aria-label="Row actions">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item href="{{ route('dashboard.users.edit', $user) }}">Edit</x-ui.dropdown-item>
                    <x-ui.dropdown-item href="/author/{{ $user->slug }}" target="_blank">View public</x-ui.dropdown-item>
                    @if ($user->id !== auth()->id())
                        <div class="my-1 border-t border-surface-200 dark:border-ink-700"></div>
                        <form method="POST" action="{{ route('dashboard.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')" class="block">
                            @csrf @method('DELETE')
                            <x-ui.dropdown-item type="submit" tone="danger">Delete</x-ui.dropdown-item>
                        </form>
                    @endif
                </x-ui.dropdown>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-4 py-10">
                <x-ui.empty-state title="No users yet">
                    <x-slot:action>
                        <x-ui.button href="{{ route('dashboard.users.create') }}" variant="primary">Create user</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </td>
        </tr>
    @endforelse
    @if ($users->hasPages())
        <x-slot:footer>{{ $users->withQueryString()->links() }}</x-slot:footer>
    @endif
</x-ui.table>
@endsection
