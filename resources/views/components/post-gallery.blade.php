@if($post->gallery && $post->gallery->images->isNotEmpty())
<div class="mt-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ $post->gallery->title }}</h2>
    @if($post->gallery->description)
    <p class="text-gray-500 mb-6">{{ $post->gallery->description }}</p>
    @endif
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($post->gallery->images as $image)
        <a href="{{ $image->image_path }}" target="_blank" class="block overflow-hidden rounded-lg">
            <img src="{{ $image->image_path }}" alt="{{ $image->alt_text ?? $image->caption ?? '' }}" class="w-full h-48 object-cover hover:scale-105 transition duration-300">
        </a>
        @endforeach
    </div>
</div>
@endif
