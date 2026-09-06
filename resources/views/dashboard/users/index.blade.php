@extends('layouts.dashboard')

@section('title', 'Users')
@section('page-title', 'Users')

@section('actions')
<x-ui.button href="{{ route('dashboard.users.create') }}" variant="primary" size="sm">New user</x-ui.button>
@endsection

@section('content')
<x-ui.table :columns="['name', 'email', 'posts', '']">
    @forelse ($users as $user)
        <tr>
            <td>
                <div class="flex items-center gap-3">
                    @if ($user->profile_image)
                        <img class="h-8 w-8 rounded-full" src="{{ $user->profile_image }}" alt="">
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-ink-800 text-white font-display text-[13px]">{{ Str::upper(Str::substr($user->name, 0, 2)) }}</span>
                    @endif
                    <div>
                        <a href="{{ route('dashboard.users.edit', $user) }}" class="font-display text-lg text-ink-900 hover:text-accent-700 transition-colors">{{ $user->name }}</a>
                        <p class="meta mt-0.5">/author/{{ $user->slug }}</p>
                    </div>
                </div>
            </td>
            <td class="meta">{{ $user->email }}</td>
            <td class="meta">{{ $user->posts_count }}</td>
            <td class="text-right whitespace-nowrap meta">
                <a href="{{ route('dashboard.users.edit', $user) }}" class="link-rise">edit</a>
                <span class="text-ink-200"> · </span>
                <a href="/author/{{ $user->slug }}" target="_blank" class="link-rise">view</a>
                @if ($user->id !== auth()->id())
                    <span class="text-ink-200"> · </span>
                    <form method="POST" action="{{ route('dashboard.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-danger-500 hover:text-danger-700 font-mono">delete</button>
                    </form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="py-10">
            <x-ui.empty-state title="No users yet">
                <x-slot:action><x-ui.button href="{{ route('dashboard.users.create') }}" variant="primary">Create user</x-ui.button></x-slot:action>
            </x-ui.empty-state>
        </td></tr>
    @endforelse
    @if ($users->hasPages())
        <x-slot:footer>{{ $users->withQueryString()->links() }}</x-slot:footer>
    @endif
</x-ui.table>
@endsection
