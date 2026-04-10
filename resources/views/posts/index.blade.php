@extends('layouts.app')

@section('content')
<div class="bg-white pt-16 pb-20 px-4 sm:px-6 lg:pt-24 lg:pb-28 lg:px-8">
    <div class="relative max-w-lg mx-auto divide-y-2 divide-gray-200 lg:max-w-6xl">
        <div>
            <h2 class="text-3xl tracking-tight font-extrabold text-gray-900 sm:text-4xl">Latest Posts</h2>
            <p class="mt-3 text-xl text-gray-500 sm:mt-4">Thoughts, ideas and stories</p>
        </div>
        <div class="mt-12 grid gap-16 pt-12 lg:grid-cols-3 lg:gap-x-5 lg:gap-y-12">
            @foreach($posts as $post)
                @include('components.post-card', ['post' => $post])
            @endforeach
        </div>

        @if($posts->hasPages())
        <nav class="mt-8 bg-white py-3 flex items-center justify-between border-t border-gray-200" aria-label="Pagination">
            <div class="hidden sm:block">
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $posts->currentPage() }}</span>
                    of <span class="font-medium">{{ $posts->lastPage() }}</span> results
                </p>
            </div>
            <div class="flex-1 flex justify-between sm:justify-end">
                @if($posts->previousPageUrl())
                <a href="{{ $posts->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition ease-in-out duration-300">Newer Posts</a>
                @endif
                @if($posts->nextPageUrl())
                <a href="{{ $posts->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition ease-in-out duration-300">Older Posts</a>
                @endif
            </div>
        </nav>
        @endif
    </div>
</div>
@endsection
