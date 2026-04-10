<div x-data="{
    images: {{ json_encode(old('images', isset($gallery) ? $gallery->images->map(fn($i) => ['path' => $i->image_path, 'caption' => $i->caption, 'alt_text' => $i->alt_text])->toArray() : [])) }},
    addImage() {
        this.images.push({ path: '', caption: '', alt_text: '' });
    },
    removeImage(index) {
        this.images.splice(index, 1);
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $gallery->title ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $gallery->description ?? '') }}</textarea>
                </div>
                <div>
                    <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-1">Cover Image URL</label>
                    <input type="text" name="cover_image" id="cover_image" value="{{ old('cover_image', $gallery->cover_image ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-medium text-gray-700">Images</h3>
                    <button type="button" @click="addImage()" class="text-sm text-emerald-600 hover:text-emerald-800">+ Add Image</button>
                </div>
                <template x-for="(image, index) in images" :key="index">
                    <div class="border border-gray-200 rounded-md p-4 mb-3">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-xs text-gray-400" x-text="'Image ' + (index + 1)"></span>
                            <button type="button" @click="removeImage(index)" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                        </div>
                        <div class="space-y-2">
                            <input type="text" :name="'images[' + index + '][path]'" x-model="image.path" placeholder="Image path" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <input type="text" :name="'images[' + index + '][caption]'" x-model="image.caption" placeholder="Caption" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <input type="text" :name="'images[' + index + '][alt_text]'" x-model="image.alt_text" placeholder="Alt text" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </template>
                <p x-show="images.length === 0" class="text-sm text-gray-400 text-center py-4">No images added yet.</p>
            </div>
        </div>

        <div class="space-y-6">
            @if(isset($gallery) && $gallery->slug)
            <div class="bg-white rounded-lg shadow p-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Shortcode</label>
                <code class="text-sm bg-gray-100 px-2 py-1 rounded">[gallery:{{ $gallery->slug }}]</code>
                <p class="text-xs text-gray-400 mt-2">Paste this in any post to embed this gallery.</p>
            </div>
            @endif

            <button type="submit" class="w-full bg-emerald-500 text-white py-2 px-4 rounded-md font-medium hover:bg-emerald-600 transition">Save Gallery</button>
        </div>
    </div>
</div>
