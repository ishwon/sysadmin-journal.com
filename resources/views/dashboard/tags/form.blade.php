<div x-data="{
    name: '{{ old('name', $tag->name ?? '') }}',
    slug: '{{ old('slug', $tag->slug ?? '') }}',
    slugManual: {{ old('slug', $tag->slug ?? '') ? 'true' : 'false' }},
    generateSlug() {
        if (this.slugManual && this.slug) return;
        this.slug = this.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="name" x-model="name" @input.debounce.500ms="generateSlug()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
                <div class="mb-4">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" id="slug" x-model="slug" @input="slugManual = true" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 font-mono text-sm" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">{{ old('description', $tag->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <label for="feature_image" class="block text-sm font-medium text-gray-700 mb-1">Feature Image URL</label>
                    <input type="text" name="feature_image" id="feature_image" value="{{ old('feature_image', $tag->feature_image ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>
                <h3 class="text-sm font-medium text-gray-700 mb-3">SEO</h3>
                <div class="space-y-3">
                    <div>
                        <label for="meta_title" class="block text-xs text-gray-500 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $tag->meta_title ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                    <div>
                        <label for="meta_description" class="block text-xs text-gray-500 mb-1">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">{{ old('meta_description', $tag->meta_description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-500 text-white py-2 px-4 rounded-md font-medium hover:bg-emerald-600 transition">Save</button>
        </div>
    </div>
</div>
