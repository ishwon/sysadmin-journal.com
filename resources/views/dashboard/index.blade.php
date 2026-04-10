@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
</div>
@endsection
