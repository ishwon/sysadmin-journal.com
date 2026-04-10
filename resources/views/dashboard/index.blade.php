@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Total Posts</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $postCount }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Published</div>
        <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $publishedCount }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Pages</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $pageCount }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Galleries</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $galleryCount }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Tags</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $tagCount }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500">Users</div>
        <div class="mt-2 text-3xl font-bold text-gray-900">{{ $userCount }}</div>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="space-y-3">
            <a href="{{ route('dashboard.posts.create') }}" class="block w-full text-left px-4 py-3 bg-emerald-50 text-emerald-700 rounded-md hover:bg-emerald-100 transition">New Post</a>
            <a href="{{ route('dashboard.pages.create') }}" class="block w-full text-left px-4 py-3 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition">New Page</a>
            <a href="{{ route('dashboard.galleries.create') }}" class="block w-full text-left px-4 py-3 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition">New Gallery</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Sitemap</h3>
        <p class="text-sm text-gray-500 mb-4">
            @if($sitemapExists)
            Sitemap is available at <a href="/sitemap.xml" target="_blank" class="text-emerald-600 hover:text-emerald-800">/sitemap.xml</a>.
            @else
            No sitemap has been generated yet.
            @endif
        </p>
        <form method="POST" action="{{ route('dashboard.sitemap.generate') }}">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-md hover:bg-emerald-600 transition">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                {{ $sitemapExists ? 'Regenerate Sitemap' : 'Generate Sitemap' }}
            </button>
        </form>
    </div>
</div>
@endsection
