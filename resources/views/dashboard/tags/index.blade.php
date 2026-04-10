@extends('layouts.dashboard')

@section('title', 'Tags')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div></div>
    <a href="{{ route('dashboard.tags.create') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-600 transition">New Tag</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posts</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($tags as $tag)
            <tr>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $tag->name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500 font-mono">/tag/{{ $tag->slug }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $tag->posts_count }}</td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    <a href="/tag/{{ $tag->slug }}" class="text-gray-500 hover:text-gray-700" target="_blank">View</a>
                    <a href="{{ route('dashboard.tags.edit', $tag) }}" class="text-emerald-600 hover:text-emerald-800">Edit</a>
                    <form method="POST" action="{{ route('dashboard.tags.destroy', $tag) }}" class="inline" onsubmit="return confirm('Delete this tag?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $tags->links() }}</div>
@endsection
