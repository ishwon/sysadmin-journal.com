<div x-data="{
    title: '{{ old('title', $page->title ?? '') }}',
    slug: '{{ old('slug', $page->slug ?? '') }}',
    slugManual: {{ old('slug', $page->slug ?? '') ? 'true' : 'false' }},
    contentFormat: '{{ old('content_format', ($page->markdown ?? null) ? 'markdown' : 'html') }}',
    async generateSlug() {
        if (this.slugManual && this.slug) return;
        const response = await fetch('{{ route('dashboard.api.slug-check') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ title: this.title, exclude_id: '{{ $page->id ?? '' }}' })
        });
        const data = await response.json();
        this.slug = data.slug;
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" x-model="title" @input.debounce.500ms="generateSlug()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500" required>
                </div>
                <div class="mb-4">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" id="slug" x-model="slug" @input="slugManual = true" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 font-mono text-sm" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content Format</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="content_format" value="html" x-model="contentFormat" class="text-emerald-500">
                            <span class="ml-2 text-sm">HTML</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="content_format" value="markdown" x-model="contentFormat" class="text-emerald-500">
                            <span class="ml-2 text-sm">Markdown</span>
                        </label>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" id="content" rows="20" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 font-mono text-sm">{{ old('content', ($page->markdown ?? null) ?: ($page->html ?? '')) }}</textarea>
                </div>
                <div>
                    <label for="custom_excerpt" class="block text-sm font-medium text-gray-700 mb-1">Custom Excerpt</label>
                    <textarea name="custom_excerpt" id="custom_excerpt" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">{{ old('custom_excerpt', $page->custom_excerpt ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="draft" {{ old('status', $page->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $page->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div>
                    <label for="feature_image" class="block text-sm font-medium text-gray-700 mb-1">Feature Image URL</label>
                    <input type="text" name="feature_image" id="feature_image" value="{{ old('feature_image', $page->feature_image ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">SEO</h3>
                <div class="space-y-3">
                    <div>
                        <label for="meta_title" class="block text-xs text-gray-500 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                    <div>
                        <label for="meta_description" class="block text-xs text-gray-500 mb-1">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 text-sm">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-500 text-white py-2 px-4 rounded-md font-medium hover:bg-emerald-600 transition">Save</button>
        </div>
    </div>
</div>
