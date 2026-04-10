@extends('layouts.dashboard')

@section('title', 'Posts')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div></div>
    <a href="{{ route('dashboard.posts.create') }}" class="bg-emerald-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-600 transition">New Post</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($posts as $post)
            <tr>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $post->title }}</div>
                    <div class="text-xs text-gray-500">/{{ $post->slug }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($post->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $post->published_at?->format('d M Y') ?? $post->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    <a href="{{ route('dashboard.posts.show', $post) }}" class="text-blue-600 hover:text-blue-800" target="_blank">Preview</a>
                    <a href="/{{ $post->slug }}" class="text-gray-500 hover:text-gray-700" target="_blank">View</a>
                    <a href="{{ route('dashboard.posts.edit', $post) }}" class="text-emerald-600 hover:text-emerald-800">Edit</a>
                    <form method="POST" action="{{ route('dashboard.posts.destroy', $post) }}" class="inline" onsubmit="return confirm('Delete this post?')">
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

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
