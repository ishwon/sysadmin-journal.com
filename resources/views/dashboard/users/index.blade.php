@extends('layouts.dashboard')

@section('title', 'Users')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div></div>
    <a href="{{ route('dashboard.users.create') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-600 transition">New User</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posts</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($users as $user)
            <tr>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        @if($user->profile_image)
                        <img class="h-8 w-8 rounded-full mr-3" src="{{ $user->profile_image }}" alt="">
                        @endif
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->slug }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $user->posts_count }}</td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    <a href="/author/{{ $user->slug }}" class="text-gray-500 hover:text-gray-700" target="_blank">View</a>
                    <a href="{{ route('dashboard.users.edit', $user) }}" class="text-emerald-600 hover:text-emerald-800">Edit</a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('dashboard.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
